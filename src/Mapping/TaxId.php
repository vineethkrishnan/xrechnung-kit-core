<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;

/**
 * A tax identifier: VAT registration number, local company-registration
 * number, or other scheme. Maps to the UBL <cac:PartyTaxScheme> structure.
 *
 * Two named constructors cover the common cases and pin the schemeId so
 * callers do not get to invent their own.
 */
final class TaxId
{
    public function __construct(
        public readonly string $companyId,
        public readonly string $schemeId,
    ) {
        if (trim($companyId) === '') {
            throw MappingDataException::emptyField('TaxId companyId');
        }
        if (trim($schemeId) === '') {
            throw MappingDataException::emptyField('TaxId schemeId');
        }
    }

    /**
     * Standard EU VAT identification number. The schemeId is "VAT" per UBL.
     */
    public static function vatId(string $vatNumber): self
    {
        return new self($vatNumber, 'VAT');
    }

    /**
     * Local company-registration identifier (e.g., German HRB number). The
     * schemeId is "FC" per UBL convention for company registration.
     */
    public static function companyRegistration(string $registrationNumber): self
    {
        return new self($registrationNumber, 'FC');
    }
}
