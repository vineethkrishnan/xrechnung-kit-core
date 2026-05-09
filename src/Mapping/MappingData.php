<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\XRechnungInvoiceTypeCode;

/**
 * Root of the typed value-object graph that flows from the consumer's mapper
 * into the generator pipeline. Once constructed, MappingData is structurally
 * trusted; downstream stages (Builder, Generator, Validator, Writer) do not
 * re-validate the shape.
 *
 * Construction is via one of the five named constructors that match the
 * document classes the kit emits at v1.0:
 *
 *   - standardInvoice      (commercial invoice, UNTDID 380)
 *   - partialInvoice       (Anzahlung, UNTDID 326; period required)
 *   - cautionInvoice       (security deposit; period and dueDate required)
 *   - creditNote           (cancellation, UNTDID 381; prior reference required)
 *   - depositCancellation  (UNTDID 381; both prior reference and period required)
 *
 * Direct __construct is allowed for advanced callers but does not enforce the
 * per-document-class invariants the named constructors check.
 *
 * Note on enum naming: the lifted XRechnungInvoiceTypeCode case names follow
 * the L3 convention rather than the UNTDID semantic - CREDIT_NOTE = 326 is
 * actually Partial Invoice, DEBIT_NOTE = 381 is actually Credit Note. The
 * named constructors here use the architecture's terminology (partialInvoice,
 * creditNote) and pin the right enum case internally; consumers should not
 * read the case names directly.
 */
final class MappingData
{
    /**
     * @param list<LineItem>      $lines
     * @param list<TaxBreakdown>  $taxes
     * @param list<PaymentMeans>  $payment
     * @param list<Attachment>    $attachments
     * @param list<Note>          $notes
     */
    public function __construct(
        public readonly DocumentMeta $meta,
        public readonly Party $seller,
        public readonly Party $buyer,
        public readonly array $lines,
        public readonly array $taxes,
        public readonly array $payment,
        public readonly DocumentTotals $totals,
        public readonly ?DocumentPeriod $period = null,
        public readonly ?BillingReference $prior = null,
        public readonly array $attachments = [],
        public readonly array $notes = [],
    ) {
        if ($lines === []) {
            throw new MappingDataException('MappingData requires at least one LineItem');
        }
        if ($taxes === []) {
            throw new MappingDataException('MappingData requires at least one TaxBreakdown');
        }

        $currency = $meta->currency;
        $this->assertCurrency('totals.lineNet',       $totals->lineNet->currency,       $currency);
        $this->assertCurrency('totals.taxableAmount', $totals->taxableAmount->currency, $currency);
        $this->assertCurrency('totals.taxAmount',     $totals->taxAmount->currency,     $currency);
        $this->assertCurrency('totals.payable',       $totals->payable->currency,       $currency);

        foreach ($lines as $i => $line) {
            if (!$line instanceof LineItem) {
                throw new MappingDataException(sprintf('lines[%d] must be a LineItem', $i));
            }
            $this->assertCurrency(sprintf('lines[%d].lineTotal', $i), $line->lineTotal->currency, $currency);
        }
        foreach ($taxes as $i => $tax) {
            if (!$tax instanceof TaxBreakdown) {
                throw new MappingDataException(sprintf('taxes[%d] must be a TaxBreakdown', $i));
            }
            $this->assertCurrency(sprintf('taxes[%d].taxableAmount', $i), $tax->taxableAmount->currency, $currency);
        }
        foreach ($payment as $i => $pm) {
            if (!$pm instanceof PaymentMeans) {
                throw new MappingDataException(sprintf('payment[%d] must be a PaymentMeans', $i));
            }
        }
        foreach ($attachments as $i => $a) {
            if (!$a instanceof Attachment) {
                throw new MappingDataException(sprintf('attachments[%d] must be an Attachment', $i));
            }
        }
        foreach ($notes as $i => $n) {
            if (!$n instanceof Note) {
                throw new MappingDataException(sprintf('notes[%d] must be a Note', $i));
            }
        }
    }

    /**
     * Standard commercial invoice (UNTDID 380). The vanilla case.
     *
     * @param list<LineItem>      $lines
     * @param list<TaxBreakdown>  $taxes
     * @param list<PaymentMeans>  $payment
     * @param list<Attachment>    $attachments
     * @param list<Note>          $notes
     */
    public static function standardInvoice(
        DocumentMeta $meta,
        Party $seller,
        Party $buyer,
        array $lines,
        array $taxes,
        array $payment,
        DocumentTotals $totals,
        ?DocumentPeriod $period = null,
        array $attachments = [],
        array $notes = [],
    ): self {
        self::assertType($meta, XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE, 'standardInvoice');
        return new self($meta, $seller, $buyer, $lines, $taxes, $payment, $totals, $period, null, $attachments, $notes);
    }

    /**
     * Partial invoice / Anzahlung (UNTDID 326). Document period (BG-14) is
     * required by BR-DE-TMP-32.
     *
     * @param list<LineItem>      $lines
     * @param list<TaxBreakdown>  $taxes
     * @param list<PaymentMeans>  $payment
     * @param list<Attachment>    $attachments
     * @param list<Note>          $notes
     */
    public static function partialInvoice(
        DocumentMeta $meta,
        Party $seller,
        Party $buyer,
        array $lines,
        array $taxes,
        array $payment,
        DocumentTotals $totals,
        DocumentPeriod $period,
        array $attachments = [],
        array $notes = [],
    ): self {
        self::assertType($meta, XRechnungInvoiceTypeCode::CREDIT_NOTE, 'partialInvoice');
        return new self($meta, $seller, $buyer, $lines, $taxes, $payment, $totals, $period, null, $attachments, $notes);
    }

    /**
     * Caution / security deposit invoice (UNTDID 380 caution variant).
     * Both period and dueDate are required.
     *
     * @param list<LineItem>      $lines
     * @param list<TaxBreakdown>  $taxes
     * @param list<PaymentMeans>  $payment
     * @param list<Attachment>    $attachments
     * @param list<Note>          $notes
     */
    public static function cautionInvoice(
        DocumentMeta $meta,
        Party $seller,
        Party $buyer,
        array $lines,
        array $taxes,
        array $payment,
        DocumentTotals $totals,
        DocumentPeriod $period,
        array $attachments = [],
        array $notes = [],
    ): self {
        self::assertType($meta, XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE, 'cautionInvoice');
        if ($meta->dueDate === null) {
            throw new MappingDataException('cautionInvoice requires meta.dueDate (BT-9)');
        }
        return new self($meta, $seller, $buyer, $lines, $taxes, $payment, $totals, $period, null, $attachments, $notes);
    }

    /**
     * Credit note / cancellation (UNTDID 381). Prior invoice reference
     * (BG-3 / BT-25) is required by BR-DE-22.
     *
     * @param list<LineItem>      $lines
     * @param list<TaxBreakdown>  $taxes
     * @param list<PaymentMeans>  $payment
     * @param list<Attachment>    $attachments
     * @param list<Note>          $notes
     */
    public static function creditNote(
        DocumentMeta $meta,
        Party $seller,
        Party $buyer,
        array $lines,
        array $taxes,
        array $payment,
        DocumentTotals $totals,
        BillingReference $prior,
        ?DocumentPeriod $period = null,
        array $attachments = [],
        array $notes = [],
    ): self {
        self::assertType($meta, XRechnungInvoiceTypeCode::DEBIT_NOTE, 'creditNote');
        return new self($meta, $seller, $buyer, $lines, $taxes, $payment, $totals, $period, $prior, $attachments, $notes);
    }

    /**
     * Deposit cancellation (UNTDID 381). Both prior invoice reference and
     * document period are required.
     *
     * @param list<LineItem>      $lines
     * @param list<TaxBreakdown>  $taxes
     * @param list<PaymentMeans>  $payment
     * @param list<Attachment>    $attachments
     * @param list<Note>          $notes
     */
    public static function depositCancellation(
        DocumentMeta $meta,
        Party $seller,
        Party $buyer,
        array $lines,
        array $taxes,
        array $payment,
        DocumentTotals $totals,
        BillingReference $prior,
        DocumentPeriod $period,
        array $attachments = [],
        array $notes = [],
    ): self {
        self::assertType($meta, XRechnungInvoiceTypeCode::DEBIT_NOTE, 'depositCancellation');
        return new self($meta, $seller, $buyer, $lines, $taxes, $payment, $totals, $period, $prior, $attachments, $notes);
    }

    private static function assertType(DocumentMeta $meta, XRechnungInvoiceTypeCode $expected, string $constructor): void
    {
        if ($meta->type !== $expected) {
            throw new MappingDataException(sprintf(
                'MappingData::%s requires meta.type = %s (%d), got %s (%d)',
                $constructor,
                $expected->name,
                $expected->value,
                $meta->type->name,
                $meta->type->value,
            ));
        }
    }

    private function assertCurrency(string $field, string $given, string $expected): void
    {
        if ($given !== $expected) {
            throw new MappingDataException(sprintf(
                'Currency mismatch on %s: %s vs document currency %s',
                $field,
                $given,
                $expected,
            ));
        }
    }
}
