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
        $entity->setInvoiceNumber($data->meta->invoiceNumber);
        $entity->setInvoiceDate($data->meta->issueDate->format('Y-m-d'));
        $entity->setCurrencyCode($data->meta->currency);
        $entity->setBuyerReferenceNumber($data->meta->buyerReference ?? '');
        $entity->setCustomerNumber($data->meta->buyerReference ?? '');
        $entity->setNote($data->meta->note ?? '');

        if ($data->prior !== null) {
            $entity->setRelatedInvoiceNumber($data->prior->invoiceNumber);
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
        $entity->setSupplierCompanyName($seller->name);
        $entity->setSupplierStreet($seller->address->street);
        $entity->setSupplierCity($seller->address->city);
        $entity->setSupplierZip($seller->address->zip);
        $entity->setSupplierCountryCode($seller->address->countryCode);
        $entity->setSupplierEmail($seller->endpointEmail ?? '');
        if ($seller->taxId !== null) {
            $entity->setSupplierVat($seller->taxId->companyId);
            $entity->setSupplierCompanyId($seller->taxId->companyId);
        }
        if ($seller->contact !== null) {
            $entity->setSupplierName($seller->contact->name ?? '');
            $entity->setSupplierPhone($seller->contact->phone ?? '');
        }

        $buyer = $data->buyer;
        $entity->setBuyerCompanyName($buyer->name);
        $entity->setBuyerStreet($buyer->address->street);
        $entity->setBuyerAdditionalStreet($buyer->address->additionalStreet ?? '');
        $entity->setBuyerCity($buyer->address->city);
        $entity->setBuyerZip($buyer->address->zip);
        $entity->setBuyerCountryCode($buyer->address->countryCode);
        $entity->setBuyerMail($buyer->endpointEmail ?? '');
        $entity->setBuyerNumber($buyer->leitwegId ?? '');
        if ($buyer->contact !== null) {
            $entity->setBuyerName($buyer->contact->name ?? '');
            $entity->setBuyerPhone($buyer->contact->phone ?? '');
            $entity->setBuyerEmail($buyer->contact->email ?? '');
        }

        $first = $data->payment[0] ?? null;
        if ($first !== null) {
            $entity->setPaymentCode($first->code->value);
            $entity->setFinancialNumber($first->iban ?? '');
            $entity->setPaymentNote($first->paymentReference ?? '');
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
            ->setItemNumber($line->id)
            ->setItemDescription($line->description)
            ->setItemResource($line->name ?? $line->description)
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
