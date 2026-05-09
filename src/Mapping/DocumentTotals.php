<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;

/**
 * Document-level monetary totals. Maps to UBL <cac:LegalMonetaryTotal>
 * and <cac:TaxTotal>.
 *
 * All amounts must share the same currency (the document currency declared
 * in DocumentMeta). The optional allowance, charge, and prepaid amounts
 * default to null and are treated as zero by the Generator.
 *
 * The kit does NOT recompute any total from the others. The consumer
 * supplies all six because consumers apply their own rounding policy and
 * because the invariants (BT-CO arithmetic) belong in the source mapper,
 * not at the public boundary.
 */
final class DocumentTotals
{
    public function __construct(
        public readonly Money $lineNet,
        public readonly Money $taxableAmount,
        public readonly Money $taxAmount,
        public readonly Money $payable,
        public readonly ?Money $allowance = null,
        public readonly ?Money $charge = null,
        public readonly ?Money $prepaid = null,
    ) {
        $currency = $lineNet->currency;
        $this->assertSameCurrency('taxableAmount', $taxableAmount, $currency);
        $this->assertSameCurrency('taxAmount', $taxAmount, $currency);
        $this->assertSameCurrency('payable', $payable, $currency);
        if ($allowance !== null) {
            $this->assertSameCurrency('allowance', $allowance, $currency);
        }
        if ($charge !== null) {
            $this->assertSameCurrency('charge', $charge, $currency);
        }
        if ($prepaid !== null) {
            $this->assertSameCurrency('prepaid', $prepaid, $currency);
        }
    }

    private function assertSameCurrency(string $field, Money $value, string $expectedCurrency): void
    {
        if ($value->currency !== $expectedCurrency) {
            throw MappingDataException::currencyMismatch($expectedCurrency, $value->currency);
        }
    }
}
