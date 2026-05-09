<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\DocumentPeriod;
use XrechnungKit\Mapping\LineItem;
use XrechnungKit\Mapping\Money;
use XrechnungKit\XRechnungTaxCategory;

final class LineItemTest extends TestCase
{
    #[Test]
    public function it_constructs_a_minimal_line_item(): void
    {
        $line = new LineItem(
            id: '1',
            description: 'Beratungsleistung',
            quantity: '4',
            unitCode: 'HUR',
            unitPrice: Money::eur('120.00'),
            lineTotal: Money::eur('480.00'),
            taxCategory: XRechnungTaxCategory::STANDARD_RATE,
            taxPercent: '19.00',
        );

        self::assertSame('1', $line->id);
        self::assertSame('HUR', $line->unitCode);
        self::assertSame('480.00', $line->lineTotal->amount);
        self::assertSame(XRechnungTaxCategory::STANDARD_RATE, $line->taxCategory);
        self::assertNull($line->name);
        self::assertNull($line->period);
    }

    #[Test]
    public function it_accepts_an_optional_name_and_period(): void
    {
        $line = new LineItem(
            id: '1',
            description: 'May-2026 hosting',
            quantity: '1',
            unitCode: 'MON',
            unitPrice: Money::eur('99.00'),
            lineTotal: Money::eur('99.00'),
            taxCategory: XRechnungTaxCategory::STANDARD_RATE,
            taxPercent: '19',
            name: 'Hosting subscription',
            period: new DocumentPeriod(
                new \DateTimeImmutable('2026-05-01'),
                new \DateTimeImmutable('2026-05-31'),
            ),
        );

        self::assertSame('Hosting subscription', $line->name);
        self::assertNotNull($line->period);
        self::assertSame('2026-05-01', $line->period->start->format('Y-m-d'));
    }

    #[Test]
    public function it_rejects_a_currency_mismatch_between_unit_price_and_line_total(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Currency mismatch/');

        new LineItem(
            id: '1',
            description: 'X',
            quantity: '1',
            unitCode: 'EA',
            unitPrice: Money::eur('100.00'),
            lineTotal: Money::of('100.00', 'USD'),
            taxCategory: XRechnungTaxCategory::STANDARD_RATE,
            taxPercent: '19',
        );
    }

    #[Test]
    public function it_rejects_an_empty_id(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/LineItem id/');

        new LineItem('', 'X', '1', 'EA', Money::eur('1'), Money::eur('1'), XRechnungTaxCategory::STANDARD_RATE, '0');
    }

    #[Test]
    public function it_rejects_a_non_decimal_quantity(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/quantity must be a decimal/');

        new LineItem('1', 'X', 'four', 'EA', Money::eur('1'), Money::eur('1'), XRechnungTaxCategory::STANDARD_RATE, '0');
    }

    #[Test]
    public function it_rejects_a_non_decimal_tax_percent(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/taxPercent must be a decimal/');

        new LineItem('1', 'X', '1', 'EA', Money::eur('1'), Money::eur('1'), XRechnungTaxCategory::STANDARD_RATE, '19%');
    }
}
