<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\DocumentMeta;
use XrechnungKit\XRechnungInvoiceTypeCode;

final class DocumentMetaTest extends TestCase
{
    #[Test]
    public function it_constructs_a_minimal_invoice_header(): void
    {
        $meta = new DocumentMeta(
            invoiceNumber: 'RE-2026-0001',
            type: XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE,
            issueDate: new \DateTimeImmutable('2026-05-01'),
            currency: 'EUR',
        );

        self::assertSame('RE-2026-0001', $meta->invoiceNumber);
        self::assertSame(XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE, $meta->type);
        self::assertSame('EUR', $meta->currency);
        self::assertNull($meta->dueDate);
        self::assertNull($meta->buyerReference);
        self::assertNull($meta->note);
    }

    #[Test]
    public function it_accepts_optional_due_date_buyer_reference_and_note(): void
    {
        $meta = new DocumentMeta(
            invoiceNumber: 'RE-2026-0001',
            type: XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE,
            issueDate: new \DateTimeImmutable('2026-05-01'),
            currency: 'EUR',
            dueDate: new \DateTimeImmutable('2026-05-31'),
            buyerReference: '04011000-12345-67',
            note: 'Net 30 days',
        );

        self::assertNotNull($meta->dueDate);
        self::assertSame('2026-05-31', $meta->dueDate->format('Y-m-d'));
        self::assertSame('04011000-12345-67', $meta->buyerReference);
        self::assertSame('Net 30 days', $meta->note);
    }

    #[Test]
    public function it_rejects_an_empty_invoice_number(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/invoiceNumber/');

        new DocumentMeta('', XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE, new \DateTimeImmutable(), 'EUR');
    }

    #[Test]
    public function it_rejects_a_non_iso_4217_currency(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Currency must be ISO 4217/');

        new DocumentMeta('RE-1', XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE, new \DateTimeImmutable(), 'eur');
    }

    #[Test]
    public function it_rejects_a_due_date_before_the_issue_date(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/dueDate .* must be on or after issueDate/');

        new DocumentMeta(
            invoiceNumber: 'RE-1',
            type: XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE,
            issueDate: new \DateTimeImmutable('2026-05-31'),
            currency: 'EUR',
            dueDate: new \DateTimeImmutable('2026-05-01'),
        );
    }
}
