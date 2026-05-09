<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Security;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\XRechnungValidator;

/**
 * Pins XXE-defence behaviour in XRechnungValidator. The validator loads XML
 * with LIBXML_NONET so external entities cannot fetch over the network, and
 * relies on PHP 8+'s default of disabled external entity loading for file
 * URI references.
 *
 * The architecture's threat model (section 16) is deliberately narrow: the
 * kit emits XML, it does not parse arbitrary user XML except to validate its
 * own freshly-generated output. These tests pin the validator's safety
 * regardless, since validateContent is now part of the public surface.
 */
final class ValidatorXxeTest extends TestCase
{
    #[Test]
    public function it_does_not_resolve_a_file_xxe_external_entity(): void
    {
        $sentinelFile = sys_get_temp_dir() . '/xrechnung-kit-xxe-sentinel-' . uniqid('', true) . '.txt';
        file_put_contents($sentinelFile, 'SENSITIVE-SENTINEL-VALUE');

        $hostileXml = '<?xml version="1.0"?>'
            . '<!DOCTYPE invoice ['
            . '<!ENTITY xxe SYSTEM "file://' . $sentinelFile . '">'
            . ']>'
            . '<invoice><note>&xxe;</note></invoice>';

        $validator = new XRechnungValidator();

        try {
            $isValid = $validator->validateContent($hostileXml);

            // The hostile XML is not a valid XRechnung, so validation must fail.
            self::assertFalse($isValid, 'Crafted XXE payload must not validate as a real XRechnung');

            // The sensitive sentinel must NOT have been embedded in any error
            // message we can observe. If the validator resolved the entity it
            // would have substituted "SENSITIVE-SENTINEL-VALUE" into the
            // document tree (and possibly into libxml errors).
            $errors = implode("\n", $validator->getErrors());
            self::assertStringNotContainsString('SENSITIVE-SENTINEL-VALUE', $errors, 'XXE entity must not have been resolved');
        } finally {
            @unlink($sentinelFile);
        }
    }

    #[Test]
    public function it_does_not_attempt_a_network_fetch_for_an_external_entity(): void
    {
        // Pointing at a non-routable address. If LIBXML_NONET is honoured the
        // validator will not attempt the connection at all; we measure
        // wall-clock time as a proxy for "did the validator try to dial out".
        $hostileXml = '<?xml version="1.0"?>'
            . '<!DOCTYPE invoice ['
            . '<!ENTITY xxe SYSTEM "http://10.255.255.1:9/blackhole">'
            . ']>'
            . '<invoice><note>&xxe;</note></invoice>';

        $validator = new XRechnungValidator();
        $start = microtime(true);
        $validator->validateContent($hostileXml);
        $elapsedSeconds = microtime(true) - $start;

        self::assertLessThan(2.0, $elapsedSeconds, 'Validator must not attempt to dial out for external entities (LIBXML_NONET)');
    }

    #[Test]
    public function it_rejects_an_empty_xml_string_with_a_clear_message(): void
    {
        $validator = new XRechnungValidator();
        $isValid = $validator->validateContent('');

        self::assertFalse($isValid);
        $errors = $validator->getErrors();
        self::assertNotEmpty($errors);
        self::assertStringContainsString('empty XML', $errors[0]);
    }
}
