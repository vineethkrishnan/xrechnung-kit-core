<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;

/**
 * Reference to a prior invoice. Required on credit notes per BR-DE-22 and on
 * deposit-cancellation documents. Maps to the UBL <cac:BillingReference>
 * structure carrying BG-3 / BT-25.
 */
final class BillingReference
{
    public function __construct(
        public readonly string $invoiceNumber,
        public readonly \DateTimeImmutable $issueDate,
    ) {
        if (trim($invoiceNumber) === '') {
            throw MappingDataException::emptyField('BillingReference invoiceNumber');
        }
    }
}
