<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\Address;
use XrechnungKit\Mapping\BillingReference;
use XrechnungKit\Mapping\DocumentMeta;
use XrechnungKit\Mapping\DocumentPeriod;
use XrechnungKit\Mapping\DocumentTotals;
use XrechnungKit\Mapping\LineItem;
use XrechnungKit\Mapping\MappingData;
use XrechnungKit\Mapping\Money;
use XrechnungKit\Mapping\Party;
use XrechnungKit\Mapping\PaymentMeans;
use XrechnungKit\Mapping\TaxBreakdown;
use XrechnungKit\XRechnungInvoiceTypeCode;
use XrechnungKit\XRechnungTaxCategory;

final class MappingDataTest extends TestCase
{
    #[Test]
    public function it_constructs_a_standard_invoice(): void
    {
        $mapping = MappingData::standardInvoice(
            meta: $this->meta(XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
        );

        self::assertSame(XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE, $mapping->meta->type);
        self::assertCount(1, $mapping->lines);
        self::assertNull($mapping->prior);
    }

    #[Test]
    public function it_constructs_a_partial_invoice_with_required_period(): void
    {
        $mapping = MappingData::partialInvoice(
            meta: $this->meta(XRechnungInvoiceTypeCode::CREDIT_NOTE),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            period: new DocumentPeriod(
                new \DateTimeImmutable('2026-05-01'),
                new \DateTimeImmutable('2026-05-31'),
            ),
        );

        self::assertNotNull($mapping->period);
    }

    #[Test]
    public function it_constructs_a_caution_invoice_when_meta_carries_due_date(): void
    {
        $mapping = MappingData::cautionInvoice(
            meta: $this->meta(
                XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE,
                dueDate: new \DateTimeImmutable('2026-06-01'),
            ),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            period: new DocumentPeriod(
                new \DateTimeImmutable('2026-05-01'),
                new \DateTimeImmutable('2026-05-31'),
            ),
        );

        self::assertNotNull($mapping->meta->dueDate);
    }

    #[Test]
    public function it_rejects_a_caution_invoice_without_a_due_date(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/cautionInvoice requires meta\.dueDate/');

        MappingData::cautionInvoice(
            meta: $this->meta(XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            period: new DocumentPeriod(
                new \DateTimeImmutable('2026-05-01'),
                new \DateTimeImmutable('2026-05-31'),
            ),
        );
    }

    #[Test]
    public function it_constructs_a_credit_note_with_prior_reference(): void
    {
        $mapping = MappingData::creditNote(
            meta: $this->meta(XRechnungInvoiceTypeCode::DEBIT_NOTE),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            prior: new BillingReference('RE-2026-0001', new \DateTimeImmutable('2026-04-01')),
        );

        self::assertNotNull($mapping->prior);
        self::assertSame('RE-2026-0001', $mapping->prior->invoiceNumber);
    }

    #[Test]
    public function it_constructs_a_deposit_cancellation_with_both_prior_and_period(): void
    {
        $mapping = MappingData::depositCancellation(
            meta: $this->meta(XRechnungInvoiceTypeCode::DEBIT_NOTE),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            prior: new BillingReference('RE-2026-0001', new \DateTimeImmutable('2026-04-01')),
            period: new DocumentPeriod(
                new \DateTimeImmutable('2026-04-01'),
                new \DateTimeImmutable('2026-04-30'),
            ),
        );

        self::assertNotNull($mapping->prior);
        self::assertNotNull($mapping->period);
    }

    #[Test]
    public function it_rejects_a_named_constructor_called_with_the_wrong_type_code(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/standardInvoice requires meta\.type/');

        MappingData::standardInvoice(
            meta: $this->meta(XRechnungInvoiceTypeCode::DEBIT_NOTE),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
        );
    }

    #[Test]
    public function it_rejects_an_empty_lines_array(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/at least one LineItem/');

        MappingData::standardInvoice(
            meta: $this->meta(XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
        );
    }

    #[Test]
    public function it_rejects_a_currency_mismatch_between_document_and_line_total(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Currency mismatch on lines\[0\]/');

        $usdLine = new LineItem(
            id: '1',
            description: 'X',
            quantity: '1',
            unitCode: 'EA',
            unitPrice: Money::of('100.00', 'USD'),
            lineTotal: Money::of('100.00', 'USD'),
            taxCategory: XRechnungTaxCategory::STANDARD_RATE,
            taxPercent: '0',
        );

        MappingData::standardInvoice(
            meta: $this->meta(XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE),
            seller: $this->seller(),
            buyer: $this->businessBuyer(),
            lines: [$usdLine],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
        );
    }

    private function meta(XRechnungInvoiceTypeCode $type, ?\DateTimeImmutable $dueDate = null): DocumentMeta
    {
        return new DocumentMeta(
            invoiceNumber: 'RE-MAPPING-001',
            type: $type,
            issueDate: new \DateTimeImmutable('2026-05-09'),
            currency: 'EUR',
            dueDate: $dueDate,
            buyerReference: '04011000-12345-67',
            note: 'MappingData test',
        );
    }

    private function seller(): Party
    {
        return Party::business(
            name: 'Beispiel Lieferant GmbH',
            address: new Address('Lieferantenstr. 1', 'Berlin', '10115', 'DE'),
        );
    }

    private function businessBuyer(): Party
    {
        return Party::business(
            name: 'Beispiel Kunde GmbH',
            address: new Address('Kundenstr. 1', 'Hamburg', '20095', 'DE'),
        );
    }

    private function line(): LineItem
    {
        return new LineItem(
            id: '1',
            description: 'Beratungsleistung',
            quantity: '4',
            unitCode: 'HUR',
            unitPrice: Money::eur('120.00'),
            lineTotal: Money::eur('480.00'),
            taxCategory: XRechnungTaxCategory::STANDARD_RATE,
            taxPercent: '19.00',
        );
    }

    private function tax(): TaxBreakdown
    {
        return new TaxBreakdown(
            category: XRechnungTaxCategory::STANDARD_RATE,
            percent: '19.00',
            taxableAmount: Money::eur('480.00'),
            taxAmount: Money::eur('91.20'),
        );
    }

    private function totals(): DocumentTotals
    {
        return new DocumentTotals(
            lineNet: Money::eur('480.00'),
            taxableAmount: Money::eur('480.00'),
            taxAmount: Money::eur('91.20'),
            payable: Money::eur('571.20'),
        );
    }
}
