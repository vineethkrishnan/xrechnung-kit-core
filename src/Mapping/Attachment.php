<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;

/**
 * A binary attachment to be embedded as base64 in the XRechnung XML
 * (BG-24, EmbeddedDocumentBinaryObject).
 *
 * The kit base64-encodes the bytes at emission; the consumer passes raw
 * binary. The size cap is enforced by the consumer (MappingData itself does
 * not impose a hard ceiling, since "reasonable size" depends on the
 * recipient portal); the kit documents recommended limits in
 * docs/mapping-data.md.
 */
final class Attachment
{
    public function __construct(
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly string $bytes,
        public readonly ?string $description = null,
    ) {
        if (trim($filename) === '') {
            throw MappingDataException::emptyField('Attachment filename');
        }
        if (preg_match('~^[a-zA-Z0-9!\#$&^_.+-]+/[a-zA-Z0-9!\#$&^_.+-]+$~', $mimeType) !== 1) {
            throw MappingDataException::invalidMimeType($mimeType);
        }
    }
}
