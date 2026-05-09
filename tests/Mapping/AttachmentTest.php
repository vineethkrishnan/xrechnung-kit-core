<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\Attachment;

final class AttachmentTest extends TestCase
{
    #[Test]
    public function it_constructs_a_minimal_attachment(): void
    {
        $attachment = new Attachment(
            filename: 'invoice.pdf',
            mimeType: 'application/pdf',
            bytes: '%PDF-1.7 binary content',
        );

        self::assertSame('invoice.pdf', $attachment->filename);
        self::assertSame('application/pdf', $attachment->mimeType);
        self::assertSame('%PDF-1.7 binary content', $attachment->bytes);
        self::assertNull($attachment->description);
    }

    #[Test]
    public function it_accepts_an_optional_description(): void
    {
        $attachment = new Attachment(
            filename: 'invoice.pdf',
            mimeType: 'application/pdf',
            bytes: 'x',
            description: 'Detailed billing breakdown',
        );

        self::assertSame('Detailed billing breakdown', $attachment->description);
    }

    /** @return list<array{0: string}> */
    public static function invalidMimeTypes(): array
    {
        return [
            ['application'],          // missing /subtype
            ['/pdf'],                 // missing type
            ['application/'],         // missing subtype
            ['application pdf'],      // space instead of slash
            [''],                     // empty
        ];
    }

    #[Test]
    #[DataProvider('invalidMimeTypes')]
    public function it_rejects_a_malformed_mime_type(string $mimeType): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/MIME type must be of the form/');

        new Attachment('invoice.pdf', $mimeType, 'x');
    }

    #[Test]
    public function it_rejects_an_empty_filename(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/filename/');

        new Attachment('', 'application/pdf', 'x');
    }
}
