<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use Override;
use Stringable;
use XrechnungKit\Exception\MappingDataException;

/**
 * A monetary amount paired with its ISO 4217 currency.
 *
 * Storage is a decimal string so float precision never enters the picture;
 * the consumer (or a future arithmetic helper) is responsible for computing
 * totals before the Money value object is constructed. This matches the
 * incoming-mapper contract: the mapper produces ready-to-emit amounts.
 *
 * Equality and currency validation are the only behaviour. Arithmetic on
 * Money is intentionally out of scope at v1.0; introducing it would require
 * pinning a decimal library (brick/math or ext-bcmath) which we have decided
 * not to take as a hard dependency yet.
 */
final class Money implements Stringable
{
    public function __construct(
        public readonly string $amount,
        public readonly string $currency,
    ) {
        if (preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw MappingDataException::invalidCurrencyCode($currency);
        }
        if (preg_match('/^-?\d+(\.\d+)?$/', $amount) !== 1) {
            throw MappingDataException::invalidDecimalAmount($amount);
        }
    }

    public static function eur(string $amount): self
    {
        return new self($amount, 'EUR');
    }

    public static function of(string $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    public function isNegative(): bool
    {
        return str_starts_with($this->amount, '-');
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->amount;
    }
}
