<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\XRechnungInvoiceTypeCode;

/**
 * Document-level header fields. Maps to the UBL <cbc:ID>, <cbc:IssueDate>,
 * <cbc:InvoiceTypeCode>, <cbc:DocumentCurrencyCode>, <cbc:DueDate>,
 * <cbc:BuyerReference>, and <cbc:Note> elements.
 */
final class DocumentMeta
{
    public function __construct(
        public readonly string $invoiceNumber,
        public readonly XRechnungInvoiceTypeCode $type,
        public readonly \DateTimeImmutable $issueDate,
        public readonly string $currency,
        public readonly ?\DateTimeImmutable $dueDate = null,
        public readonly ?string $buyerReference = null,
        public readonly ?string $note = null,
    ) {
        if (trim($invoiceNumber) === '') {
            throw MappingDataException::emptyField('DocumentMeta invoiceNumber');
        }
        if (preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw MappingDataException::invalidCurrencyCode($currency);
        }
        if ($dueDate !== null && $dueDate < $issueDate) {
            throw new MappingDataException(sprintf(
                'DocumentMeta dueDate (%s) must be on or after issueDate (%s)',
                $dueDate->format('Y-m-d'),
                $issueDate->format('Y-m-d'),
            ));
        }
    }
}
