<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\Money;

final class MoneyTest extends TestCase
{
    #[Test]
    public function it_constructs_a_simple_eur_amount(): void
    {
        $money = Money::eur('100.00');

        self::assertSame('100.00', $money->amount);
        self::assertSame('EUR', $money->currency);
        self::assertFalse($money->isNegative());
    }

    #[Test]
    public function it_recognises_negative_amounts(): void
    {
        self::assertTrue(Money::eur('-19.50')->isNegative());
        self::assertFalse(Money::eur('0')->isNegative());
        self::assertFalse(Money::eur('0.00')->isNegative());
    }

    #[Test]
    public function it_compares_money_by_amount_and_currency(): void
    {
        self::assertTrue(Money::eur('100.00')->equals(Money::eur('100.00')));
        self::assertFalse(Money::eur('100.00')->equals(Money::eur('100')));
        self::assertFalse(Money::eur('100.00')->equals(Money::of('100.00', 'USD')));
    }

    #[Test]
    public function it_stringifies_to_the_amount(): void
    {
        self::assertSame('42.50', (string) Money::eur('42.50'));
    }

    /** @return list<array{0: string}> */
    public static function invalidCurrencies(): array
    {
        return [
            ['eur'],          // lower-case
            ['EU'],           // too short
            ['EURO'],         // too long
            ['EU2'],          // contains digit
            [''],             // empty
        ];
    }

    #[Test]
    #[DataProvider('invalidCurrencies')]
    public function it_rejects_non_iso_4217_currency(string $currency): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Currency must be ISO 4217/');

        new Money('100.00', $currency);
    }

    /** @return list<array{0: string}> */
    public static function invalidAmounts(): array
    {
        return [
            ['100,00'],       // comma decimal
            ['1.2.3'],        // multiple dots
            ['abc'],          // not a number
            ['--5'],          // double minus
            [''],             // empty
            ['+5'],           // explicit plus not allowed
        ];
    }

    #[Test]
    #[DataProvider('invalidAmounts')]
    public function it_rejects_non_decimal_amount(string $amount): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Money amount must be a decimal string/');

        new Money($amount, 'EUR');
    }
}
