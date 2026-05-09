<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

use XrechnungKit\Exception\MappingDataException;

/**
 * A free-text note attached to the document. Maps to UBL <cbc:Note> (BT-22).
 *
 * Notes are emitted verbatim. XML special characters in the text are
 * escaped at the substitution boundary by the Generator.
 */
final class Note
{
    public function __construct(public readonly string $text)
    {
        if (trim($text) === '') {
            throw MappingDataException::emptyField('Note text');
        }
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
