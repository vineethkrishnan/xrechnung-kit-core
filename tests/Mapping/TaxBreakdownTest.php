<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\Money;
use XrechnungKit\Mapping\TaxBreakdown;
use XrechnungKit\XRechnungTaxCategory;

final class TaxBreakdownTest extends TestCase
{
    #[Test]
    public function it_constructs_a_standard_rate_subtotal(): void
    {
        $breakdown = new TaxBreakdown(
            category: XRechnungTaxCategory::STANDARD_RATE,
            percent: '19.00',
            taxableAmount: Money::eur('480.00'),
            taxAmount: Money::eur('91.20'),
        );

        self::assertSame(XRechnungTaxCategory::STANDARD_RATE, $breakdown->category);
        self::assertSame('19.00', $breakdown->percent);
        self::assertSame('480.00', $breakdown->taxableAmount->amount);
    }

    #[Test]
    public function it_constructs_a_zero_rated_subtotal(): void
    {
        $breakdown = new TaxBreakdown(
            category: XRechnungTaxCategory::ZERO_RATED_GOODS,
            percent: '0',
            taxableAmount: Money::eur('100.00'),
            taxAmount: Money::eur('0.00'),
        );

        self::assertSame('0', $breakdown->percent);
    }

    #[Test]
    public function it_rejects_a_currency_mismatch(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Currency mismatch/');

        new TaxBreakdown(
            category: XRechnungTaxCategory::STANDARD_RATE,
            percent: '19',
            taxableAmount: Money::eur('100.00'),
            taxAmount: Money::of('19.00', 'USD'),
        );
    }

    #[Test]
    public function it_rejects_a_non_decimal_percent(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/TaxBreakdown percent/');

        new TaxBreakdown(
            category: XRechnungTaxCategory::STANDARD_RATE,
            percent: 'nineteen',
            taxableAmount: Money::eur('100.00'),
            taxAmount: Money::eur('19.00'),
        );
    }
}
