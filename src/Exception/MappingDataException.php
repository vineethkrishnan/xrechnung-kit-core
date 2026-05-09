<?php

declare(strict_types=1);

namespace XrechnungKit\Exception;

/**
 * Thrown at MappingData (or any of its component value objects) construction
 * when an invariant is violated: invalid country code, negative quantity,
 * malformed Leitweg-ID, currency mismatch, etc.
 *
 * The message must be precise enough that the caller can fix the input
 * without consulting the source. Static factory methods build well-formed
 * messages for the common cases.
 */
final class MappingDataException extends XRechnungKitException
{
    public static function invalidCountryCode(string $given): self
    {
        return new self(sprintf('Country code must be ISO 3166-1 alpha-2 (two uppercase letters), got: %s', self::quote($given)));
    }

    public static function invalidCurrencyCode(string $given): self
    {
        return new self(sprintf('Currency must be ISO 4217 (three uppercase letters), got: %s', self::quote($given)));
    }

    public static function invalidDecimalAmount(string $given): self
    {
        return new self(sprintf('Money amount must be a decimal string with optional leading minus sign, got: %s', self::quote($given)));
    }

    public static function currencyMismatch(string $left, string $right): self
    {
        return new self(sprintf('Currency mismatch: %s vs %s', $left, $right));
    }

    public static function emptyField(string $field): self
    {
        return new self(sprintf('%s must be a non-empty string', $field));
    }

    public static function invalidPeriod(\DateTimeInterface $start, \DateTimeInterface $end): self
    {
        return new self(sprintf(
            'Document period end (%s) must be on or after start (%s)',
            $end->format('Y-m-d'),
            $start->format('Y-m-d'),
        ));
    }

    public static function invalidMimeType(string $given): self
    {
        return new self(sprintf('MIME type must be of the form type/subtype, got: %s', self::quote($given)));
    }

    public static function invalidLeitwegId(string $given): self
    {
        return new self(sprintf('Leitweg-ID must match /^\d{2,12}-[A-Za-z0-9]{1,30}-\d{2}$/ (BT-10), got: %s', self::quote($given)));
    }

    public static function invalidIban(string $given): self
    {
        return new self(sprintf('IBAN must be 2 letters + 2 digits + 11..30 alphanumeric chars (no spaces), got: %s', self::quote($given)));
    }

    public static function invalidBic(string $given): self
    {
        return new self(sprintf('BIC must be 4 letters + 2 letters + 2 alphanumeric (+ optional 3 alphanumeric), got: %s', self::quote($given)));
    }

    public static function missingMandateForDirectDebit(): self
    {
        return new self('SEPA Direct Debit (code 59) requires a mandate reference (BT-89)');
    }

    private static function quote(string $value): string
    {
        return '"' . str_replace('"', '\\"', $value) . '"';
    }
}
