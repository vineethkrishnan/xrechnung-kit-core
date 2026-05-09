<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use DateTimeImmutable;
use XrechnungKit\Exception\MappingDataException;

/**
 * A delivery / billing period. Maps to UBL <cac:InvoicePeriod> (BG-14 at the
 * document level, BG-26 at the line-item level).
 *
 * Required by partial / Anzahlung invoices and by deposit-cancellation
 * documents (BR-DE-TMP-32). MappingData enforces "required for these
 * document classes" at construction; the period itself only enforces that
 * end >= start.
 */
final class DocumentPeriod
{
    public function __construct(
        public readonly DateTimeImmutable $start,
        public readonly DateTimeImmutable $end,
    ) {
        if ($end < $start) {
            throw MappingDataException::invalidPeriod($start, $end);
        }
    }
}
