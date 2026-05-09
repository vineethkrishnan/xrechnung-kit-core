<?php

declare(strict_types=1);

namespace XrechnungKit\Builder;

use XrechnungKit\Mapping\LineItem;
use XrechnungKit\Mapping\MappingData;
use XrechnungKit\XRechnungEntity;
use XrechnungKit\XRechnungInvoiceLineItem;
use XrechnungKit\XRechnungInvoiceTypeCode;

/**
 * Bridges the typed MappingData value-object graph to the lifted L3
 * XRechnungEntity so the existing Generator pipeline can consume MappingData
 * without rewriting the placeholder substitution layer.
 *
 * Per architecture section 6.1: pure function. Same input, same output. No
 * filesystem I/O, no clock reads, no global state. Deterministic.
 *
 * v1.0 scope: transcribes fields from the VO graph into the entity's
 * setter-based shape and picks the right L3 invoiceType string ("invoice",
 * "caution", "cancel") that drives template selection in the Generator.
 *
 * The Generator transition is intentionally additive: existing callers that
 * construct XRechnungEntity directly continue to work. New callers should
 * prefer MappingData::standardInvoice(...) etc. and pipe through this
 * Builder. A future commit may deprecate the direct setter pattern.
 */
final class XRechnungBuilder
{
    public static function buildEntity(MappingData $data): XRechnungEntity
    {
        $entity = new XRechnungEntity();

        $entity->setInvoiceType(self::deriveInvoiceTypeString($data));
        $entity->setTypeCode($data->meta->type->value);
        $entity->setInvoiceNumber(self::sanitize($data->meta->invoiceNumber));
        $entity->setInvoiceDate($data->meta->issueDate->format('Y-m-d'));
        $entity->setCurrencyCode($data->meta->currency);
        $entity->setBuyerReferenceNumber(self::sanitize($data->meta->buyerReference ?? ''));
        $entity->setCustomerNumber(self::sanitize($data->meta->buyerReference ?? ''));
        $entity->setNote(self::sanitize($data->meta->note ?? ''));

        if ($data->prior !== null) {
            $entity->setRelatedInvoiceNumber(self::sanitize($data->prior->invoiceNumber));
        }

        $tax = $data->taxes[0] ?? null;
        if ($tax !== null) {
            $entity->setTaxCategory($tax->category->value);
            $entity->setTax($tax->percent);
            $entity->setTaxScheme('VAT');
        }

        $entity->setNetAmount($data->totals->lineNet->amount);
        $entity->setTaxAmount($data->totals->taxAmount->amount);
        $entity->setGrossAmount(self::computeGross($data));
        $entity->setPayableAmount($data->totals->payable->amount);
        $entity->setDownPayment($data->totals->prepaid !== null ? $data->totals->prepaid->amount : '0.00');

        $seller = $data->seller;
        $entity->setSupplierCompanyName(self::sanitize($seller->name));
        $entity->setSupplierStreet(self::sanitize($seller->address->street));
        $entity->setSupplierCity(self::sanitize($seller->address->city));
        $entity->setSupplierZip(self::sanitize($seller->address->zip));
        $entity->setSupplierCountryCode($seller->address->countryCode);
        $entity->setSupplierEmail(self::sanitize($seller->endpointEmail ?? ''));
        if ($seller->taxId !== null) {
            $entity->setSupplierVat(self::sanitize($seller->taxId->companyId));
            $entity->setSupplierCompanyId(self::sanitize($seller->taxId->companyId));
        }
        if ($seller->contact !== null) {
            $entity->setSupplierName(self::sanitize($seller->contact->name ?? ''));
            $entity->setSupplierPhone(self::sanitize($seller->contact->phone ?? ''));
        }

        $buyer = $data->buyer;
        $entity->setBuyerCompanyName(self::sanitize($buyer->name));
        $entity->setBuyerStreet(self::sanitize($buyer->address->street));
        $entity->setBuyerAdditionalStreet(self::sanitize($buyer->address->additionalStreet ?? ''));
        $entity->setBuyerCity(self::sanitize($buyer->address->city));
        $entity->setBuyerZip(self::sanitize($buyer->address->zip));
        $entity->setBuyerCountryCode($buyer->address->countryCode);
        $entity->setBuyerMail(self::sanitize($buyer->endpointEmail ?? ''));
        $entity->setBuyerNumber(self::sanitize($buyer->leitwegId ?? ''));
        if ($buyer->contact !== null) {
            $entity->setBuyerName(self::sanitize($buyer->contact->name ?? ''));
            $entity->setBuyerPhone(self::sanitize($buyer->contact->phone ?? ''));
            $entity->setBuyerEmail(self::sanitize($buyer->contact->email ?? ''));
        }

        $first = $data->payment[0] ?? null;
        if ($first !== null) {
            $entity->setPaymentCode($first->code->value);
            $entity->setFinancialNumber(self::sanitize($first->iban ?? ''));
            $entity->setPaymentNote(self::sanitize($first->paymentReference ?? ''));
        }

        $entity->setCautionDocuments([]);
        $entity->setDepositDocuments([]);

        foreach ($data->lines as $line) {
            $entity->addLineItem(self::buildLineItem($line));
        }

        return $entity;
    }

    private static function buildLineItem(LineItem $line): XRechnungInvoiceLineItem
    {
        $item = (new XRechnungInvoiceLineItem())
            ->setItemNumber(self::sanitize($line->id))
            ->setItemDescription(self::sanitize($line->description))
            ->setItemResource(self::sanitize($line->name ?? $line->description))
            ->setItemQuantity($line->quantity)
            ->setItemUnitPrice($line->unitPrice->amount)
            ->setItemPrice($line->lineTotal->amount)
            ->setItemTaxCategory($line->taxCategory->value)
            ->setItemTax($line->taxPercent)
            ->setItemTaxScheme('VAT')
            ->setItemAllowanceCharge(0);

        if ($line->period !== null) {
            $item->setItemStartDate($line->period->start->format('Y-m-d'));
            $item->setItemEndDate($line->period->end->format('Y-m-d'));
        } else {
            $item->setItemStartDate('');
            $item->setItemEndDate('');
        }

        return $item;
    }

    /**
     * Defense-in-depth for the str_replace based template substitution in
     * the lifted Generator. Two passes:
     *
     * 1. Strip XML 1.0 forbidden control characters (\x00-\x08, \x0B, \x0C,
     *    \x0E-\x1F). These bytes are illegal in XML 1.0 and would produce
     *    invalid output that the validator quarantines; stripping them
     *    pre-emptively is friendlier than failing late.
     *
     * 2. XML-escape the five reserved characters (& < > " ') via
     *    htmlspecialchars with ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE.
     *    The L3 generator inserts values via str_replace which does no
     *    escaping; without this pass, hostile or accidental special
     *    characters in string fields would emit malformed XML and trigger
     *    the quarantine path.
     *
     * Numeric / date / enum-coded fields skip this helper because they go
     * through their own constrained representations (Money decimal string,
     * DateTimeImmutable format, backed enum value) and do not contain XML
     * specials by construction.
     */
    private static function sanitize(string $value): string
    {
        $stripped = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);
        if ($stripped === null) {
            $stripped = $value;
        }
        return htmlspecialchars($stripped, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Picks the L3 invoiceType string that drives template selection in the
     * Generator. The architecture's five document classes collapse onto the
     * three L3 templates as follows:
     *
     *   - standardInvoice                       -> "invoice"
     *   - cautionInvoice / partialInvoice       -> "caution"  (L3 deposit
     *                                              already aliases to caution
     *                                              in the Generator)
     *   - creditNote / depositCancellation      -> "cancel"
     */
    private static function deriveInvoiceTypeString(MappingData $data): string
    {
        if ($data->meta->type === XRechnungInvoiceTypeCode::DEBIT_NOTE) {
            return 'cancel';
        }
        if ($data->meta->type === XRechnungInvoiceTypeCode::CREDIT_NOTE) {
            return 'caution';
        }
        if ($data->meta->type === XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE
            && $data->period !== null
            && $data->meta->dueDate !== null
        ) {
            return 'caution';
        }
        return 'invoice';
    }

    /**
     * Computes the UBL TaxInclusiveAmount (= taxableAmount + taxAmount).
     * v1.0 uses native float arithmetic with two-decimal rounding because
     * the Money VO stores amounts as decimal strings without exposing
     * arithmetic; the kit deliberately does not pin a decimal-math
     * dependency yet. Acceptable accuracy for amounts under ~1M EUR per
     * the architecture's performance section. A future DocumentTotals
     * field can replace this computation if the consumer needs explicit
     * control over the gross.
     */
    private static function computeGross(MappingData $data): string
    {
        $taxable = (float) $data->totals->taxableAmount->amount;
        $tax = (float) $data->totals->taxAmount->amount;
        return number_format($taxable + $tax, 2, '.', '');
    }
}
