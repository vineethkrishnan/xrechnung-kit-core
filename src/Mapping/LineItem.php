<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\XRechnungTaxCategory;

/**
 * One line on the invoice. Maps to UBL <cac:InvoiceLine>.
 *
 * Both unitPrice (BT-146, PriceAmount per unit) and lineTotal (BT-131,
 * LineExtensionAmount = qty * unitPrice) are stored. The kit does not
 * recompute one from the other so consumers can apply their own rounding
 * policy and the VO stays a faithful record of what the consumer intended
 * to emit. They must share a currency.
 */
final class LineItem
{
    private const DECIMAL_PATTERN = '/^-?\d+(\.\d+)?$/';

    public function __construct(
        public readonly string $id,
        public readonly string $description,
        public readonly string $quantity,
        public readonly string $unitCode,
        public readonly Money $unitPrice,
        public readonly Money $lineTotal,
        public readonly XRechnungTaxCategory $taxCategory,
        public readonly string $taxPercent,
        public readonly ?string $name = null,
        public readonly ?DocumentPeriod $period = null,
    ) {
        if (trim($id) === '') {
            throw MappingDataException::emptyField('LineItem id');
        }
        if (trim($description) === '') {
            throw MappingDataException::emptyField('LineItem description');
        }
        if (trim($unitCode) === '') {
            throw MappingDataException::emptyField('LineItem unitCode');
        }
        if (preg_match(self::DECIMAL_PATTERN, $quantity) !== 1) {
            throw MappingDataException::invalidDecimal('LineItem quantity', $quantity);
        }
        if (preg_match(self::DECIMAL_PATTERN, $taxPercent) !== 1) {
            throw MappingDataException::invalidDecimal('LineItem taxPercent', $taxPercent);
        }
        if ($unitPrice->currency !== $lineTotal->currency) {
            throw MappingDataException::currencyMismatch($unitPrice->currency, $lineTotal->currency);
        }
    }
}
