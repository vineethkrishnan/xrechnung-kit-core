<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;

/**
 * A postal address. Maps to the UBL <cac:PostalAddress> structure used in
 * both AccountingSupplierParty and AccountingCustomerParty.
 */
final class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $zip,
        public readonly string $countryCode,
        public readonly ?string $additionalStreet = null,
    ) {
        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1) {
            throw MappingDataException::invalidCountryCode($countryCode);
        }
        if (trim($street) === '') {
            throw MappingDataException::emptyField('street');
        }
        if (trim($city) === '') {
            throw MappingDataException::emptyField('city');
        }
        if (trim($zip) === '') {
            throw MappingDataException::emptyField('zip');
        }
    }
}
