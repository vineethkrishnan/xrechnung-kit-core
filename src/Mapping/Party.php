<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;

/**
 * A party on the invoice: seller (AccountingSupplierParty) or buyer
 * (AccountingCustomerParty). One VO covers both roles; the role-aware
 * named constructors enforce the role-specific invariants:
 *
 *   - Party::business(...)              for non-government buyers and for sellers
 *   - Party::publicAdministration(...)  for German federal recipients (BT-10 Leitweg-ID required)
 *
 * Direct construction via __construct is allowed but will not enforce the
 * role-specific invariants; consumers should prefer the named constructors.
 */
final class Party
{
    /**
     * Leitweg-ID format per the German federal e-invoicing portal:
     * 2..12 digits, dash, 1..30 alphanumeric, dash, 2 digits.
     */
    public const LEITWEG_ID_PATTERN = '/^\d{2,12}-[A-Za-z0-9]{1,30}-\d{2}$/';

    public function __construct(
        public readonly string $name,
        public readonly Address $address,
        public readonly ?TaxId $taxId = null,
        public readonly ?Contact $contact = null,
        public readonly ?string $leitwegId = null,
        public readonly ?string $endpointEmail = null,
    ) {
        if (trim($name) === '') {
            throw MappingDataException::emptyField('Party name');
        }
        if ($leitwegId !== null && preg_match(self::LEITWEG_ID_PATTERN, $leitwegId) !== 1) {
            throw MappingDataException::invalidLeitwegId($leitwegId);
        }
    }

    /**
     * Construct a non-government party. No Leitweg-ID; either side of the invoice.
     */
    public static function business(
        string $name,
        Address $address,
        ?TaxId $taxId = null,
        ?Contact $contact = null,
        ?string $endpointEmail = null,
    ): self {
        return new self(
            name: $name,
            address: $address,
            taxId: $taxId,
            contact: $contact,
            leitwegId: null,
            endpointEmail: $endpointEmail,
        );
    }

    /**
     * Construct a German public-administration buyer. Leitweg-ID (BT-10) is
     * required; the kit rejects construction without one because the
     * recipient portal will reject the invoice without it.
     */
    public static function publicAdministration(
        string $name,
        Address $address,
        string $leitwegId,
        ?Contact $contact = null,
        ?string $endpointEmail = null,
    ): self {
        return new self(
            name: $name,
            address: $address,
            taxId: null,
            contact: $contact,
            leitwegId: $leitwegId,
            endpointEmail: $endpointEmail,
        );
    }
}
