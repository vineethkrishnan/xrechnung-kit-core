<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\XRechnungTaxCategory;

/**
 * One row of the document-level tax subtotal. Maps to UBL
 * <cac:TaxSubtotal> inside <cac:TaxTotal>.
 *
 * For an invoice with multiple VAT rates (BR-CO-18) MappingData carries
 * one TaxBreakdown per (category, percent) combination. The Generator
 * sorts them by (category, percent) ascending so the same input always
 * produces the same XML.
 */
final class TaxBreakdown
{
    private const DECIMAL_PATTERN = '/^-?\d+(\.\d+)?$/';

    public function __construct(
        public readonly XRechnungTaxCategory $category,
        public readonly string $percent,
        public readonly Money $taxableAmount,
        public readonly Money $taxAmount,
    ) {
        if (preg_match(self::DECIMAL_PATTERN, $percent) !== 1) {
            throw MappingDataException::invalidDecimal('TaxBreakdown percent', $percent);
        }
        if ($taxableAmount->currency !== $taxAmount->currency) {
            throw MappingDataException::currencyMismatch($taxableAmount->currency, $taxAmount->currency);
        }
    }
}
