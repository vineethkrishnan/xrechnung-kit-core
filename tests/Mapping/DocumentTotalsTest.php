<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\DocumentTotals;
use XrechnungKit\Mapping\Money;

final class DocumentTotalsTest extends TestCase
{
    #[Test]
    public function it_constructs_minimal_totals_without_optionals(): void
    {
        $totals = new DocumentTotals(
            lineNet: Money::eur('480.00'),
            taxableAmount: Money::eur('480.00'),
            taxAmount: Money::eur('91.20'),
            payable: Money::eur('571.20'),
        );

        self::assertSame('480.00', $totals->lineNet->amount);
        self::assertSame('571.20', $totals->payable->amount);
        self::assertNull($totals->allowance);
        self::assertNull($totals->charge);
        self::assertNull($totals->prepaid);
    }

    #[Test]
    public function it_accepts_optional_allowance_charge_and_prepaid(): void
    {
        $totals = new DocumentTotals(
            lineNet: Money::eur('1000.00'),
            taxableAmount: Money::eur('900.00'),
            taxAmount: Money::eur('171.00'),
            payable: Money::eur('1071.00'),
            allowance: Money::eur('100.00'),
            charge: Money::eur('0.00'),
            prepaid: Money::eur('0.00'),
        );

        self::assertNotNull($totals->allowance);
        self::assertSame('100.00', $totals->allowance->amount);
    }

    #[Test]
    public function it_rejects_a_currency_mismatch_on_any_required_field(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Currency mismatch/');

        new DocumentTotals(
            lineNet: Money::eur('100.00'),
            taxableAmount: Money::of('100.00', 'USD'),
            taxAmount: Money::eur('19.00'),
            payable: Money::eur('119.00'),
        );
    }

    #[Test]
    public function it_rejects_a_currency_mismatch_on_an_optional_field(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Currency mismatch/');

        new DocumentTotals(
            lineNet: Money::eur('100.00'),
            taxableAmount: Money::eur('100.00'),
            taxAmount: Money::eur('19.00'),
            payable: Money::eur('119.00'),
            prepaid: Money::of('10.00', 'USD'),
        );
    }
}
