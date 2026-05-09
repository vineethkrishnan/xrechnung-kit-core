<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\Address;
use XrechnungKit\Mapping\Contact;
use XrechnungKit\Mapping\Party;
use XrechnungKit\Mapping\TaxId;

final class PartyTest extends TestCase
{
    #[Test]
    public function it_constructs_a_business_party_with_optional_tax_id_and_contact(): void
    {
        $party = Party::business(
            name: 'Beispiel GmbH',
            address: new Address('Musterstr. 1', 'Berlin', '10115', 'DE'),
            taxId: TaxId::vatId('DE123456789'),
            contact: new Contact(name: 'Anna', email: 'anna@example.de'),
            endpointEmail: 'invoice@example.de',
        );

        self::assertSame('Beispiel GmbH', $party->name);
        self::assertSame('DE', $party->address->countryCode);
        self::assertNotNull($party->taxId);
        self::assertSame('VAT', $party->taxId->schemeId);
        self::assertNotNull($party->contact);
        self::assertSame('Anna', $party->contact->name);
        self::assertSame('invoice@example.de', $party->endpointEmail);
        self::assertNull($party->leitwegId);
    }

    #[Test]
    public function it_constructs_a_public_administration_buyer_with_leitweg_id(): void
    {
        $party = Party::publicAdministration(
            name: 'Bundesamt fuer XYZ',
            address: new Address('Behoerdenweg 7', 'Bonn', '53113', 'DE'),
            leitwegId: '04011000-12345-67',
        );

        self::assertSame('04011000-12345-67', $party->leitwegId);
        self::assertNull($party->taxId);
    }

    #[Test]
    public function it_rejects_an_empty_name(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Party name/');

        Party::business('', new Address('Musterstr. 1', 'Berlin', '10115', 'DE'));
    }

    #[Test]
    public function it_rejects_a_whitespace_only_name(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Party name/');

        Party::business('   ', new Address('Musterstr. 1', 'Berlin', '10115', 'DE'));
    }

    /** @return list<array{0: string}> */
    public static function validLeitwegIds(): array
    {
        return [
            ['04011000-12345-67'],
            ['12-A-99'],                               // shortest form
            ['123456789012-' . str_repeat('A', 30) . '-99'],  // longest form
            ['99-abc123XYZ-01'],                       // mixed alphanumeric
        ];
    }

    #[Test]
    #[DataProvider('validLeitwegIds')]
    public function it_accepts_well_formed_leitweg_ids(string $leitwegId): void
    {
        $party = Party::publicAdministration(
            name: 'Behoerde',
            address: new Address('Strasse 1', 'Stadt', '12345', 'DE'),
            leitwegId: $leitwegId,
        );

        self::assertSame($leitwegId, $party->leitwegId);
    }

    /** @return list<array{0: string}> */
    public static function invalidLeitwegIds(): array
    {
        return [
            [''],                                  // empty
            ['1-A-99'],                            // first segment under 2 digits
            ['1234567890123-A-99'],                // first segment over 12 digits
            ['12--99'],                            // empty middle segment
            ['12-A-9'],                            // last segment 1 digit instead of 2
            ['12-A-999'],                          // last segment 3 digits
            ['12-A-AB'],                           // last segment letters
            ['12_A_99'],                           // wrong separator
            ['12-A!-99'],                          // illegal char in middle segment
            ['12-' . str_repeat('A', 31) . '-99'], // middle segment over 30 chars
        ];
    }

    #[Test]
    #[DataProvider('invalidLeitwegIds')]
    public function it_rejects_malformed_leitweg_ids(string $leitwegId): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Leitweg-ID must match/');

        Party::publicAdministration(
            name: 'Behoerde',
            address: new Address('Strasse 1', 'Stadt', '12345', 'DE'),
            leitwegId: $leitwegId,
        );
    }
}
