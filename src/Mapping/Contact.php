<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

/**
 * A point-of-contact at a Party. Maps to the UBL <cac:Contact> element.
 *
 * All fields are optional; EN 16931 does not require any of them at the
 * Contact level. Validation is deferred to MappingData where the document
 * context (e.g., public-administration buyer) may require specific subsets.
 */
final class Contact
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
    ) {
    }
}
