<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;

/**
 * One payment-means record. Maps to the UBL <cac:PaymentMeans> structure
 * (BG-16). The named constructors enforce the per-code invariants:
 *
 *   - sepaCreditTransfer / bankTransfer require an IBAN
 *   - sepaDirectDebit additionally requires a mandate reference (BT-89)
 *   - creditCard / bankCard accept optional masked PAN and holder name
 *   - cash takes only an optional payment reference
 *
 * Direct __construct is allowed but does not enforce the per-code invariants;
 * consumers should prefer the named constructors.
 */
final class PaymentMeans
{
    /**
     * Loose IBAN syntax: 2 letters (country) + 2 check digits + 11..30
     * alphanumeric (BBAN). The full mod-97 check is intentionally not
     * performed here; consumers that need it can run it before constructing.
     */
    public const IBAN_PATTERN = '/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/';

    /**
     * BIC (ISO 9362): 4 letters bank code + 2 letters country + 2 alphanum
     * location + optional 3 alphanum branch.
     */
    public const BIC_PATTERN = '/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/';

    public function __construct(
        public readonly PaymentMeansCode $code,
        public readonly ?string $iban = null,
        public readonly ?string $bic = null,
        public readonly ?string $accountName = null,
        public readonly ?string $mandateReference = null,
        public readonly ?string $paymentReference = null,
        public readonly ?string $cardLast4 = null,
        public readonly ?string $cardHolderName = null,
    ) {
        if ($iban !== null && preg_match(self::IBAN_PATTERN, $iban) !== 1) {
            throw MappingDataException::invalidIban($iban);
        }
        if ($bic !== null && preg_match(self::BIC_PATTERN, $bic) !== 1) {
            throw MappingDataException::invalidBic($bic);
        }
    }

    public static function sepaCreditTransfer(
        string $iban,
        ?string $bic = null,
        ?string $accountName = null,
        ?string $paymentReference = null,
    ): self {
        return new self(
            code: PaymentMeansCode::SEPA_CREDIT_TRANSFER,
            iban: $iban,
            bic: $bic,
            accountName: $accountName,
            paymentReference: $paymentReference,
        );
    }

    public static function sepaDirectDebit(
        string $iban,
        string $mandateReference,
        ?string $bic = null,
        ?string $accountName = null,
    ): self {
        if (trim($mandateReference) === '') {
            throw MappingDataException::missingMandateForDirectDebit();
        }
        return new self(
            code: PaymentMeansCode::SEPA_DIRECT_DEBIT,
            iban: $iban,
            bic: $bic,
            accountName: $accountName,
            mandateReference: $mandateReference,
        );
    }

    public static function bankTransfer(
        string $iban,
        ?string $bic = null,
        ?string $accountName = null,
        ?string $paymentReference = null,
    ): self {
        return new self(
            code: PaymentMeansCode::BANK_TRANSFER,
            iban: $iban,
            bic: $bic,
            accountName: $accountName,
            paymentReference: $paymentReference,
        );
    }

    public static function creditTransfer(
        string $iban,
        ?string $bic = null,
        ?string $accountName = null,
        ?string $paymentReference = null,
    ): self {
        return new self(
            code: PaymentMeansCode::CREDIT_TRANSFER,
            iban: $iban,
            bic: $bic,
            accountName: $accountName,
            paymentReference: $paymentReference,
        );
    }

    public static function creditCard(
        ?string $cardLast4 = null,
        ?string $cardHolderName = null,
    ): self {
        return new self(
            code: PaymentMeansCode::CREDIT_CARD,
            cardLast4: $cardLast4,
            cardHolderName: $cardHolderName,
        );
    }

    public static function bankCard(
        ?string $cardLast4 = null,
        ?string $cardHolderName = null,
    ): self {
        return new self(
            code: PaymentMeansCode::BANK_CARD,
            cardLast4: $cardLast4,
            cardHolderName: $cardHolderName,
        );
    }

    public static function cash(?string $paymentReference = null): self
    {
        return new self(
            code: PaymentMeansCode::CASH,
            paymentReference: $paymentReference,
        );
    }
}
