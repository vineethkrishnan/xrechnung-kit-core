<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\Address;

final class AddressTest extends TestCase
{
    #[Test]
    public function it_constructs_a_minimal_address(): void
    {
        $address = new Address('Musterstr. 1', 'Berlin', '10115', 'DE');

        self::assertSame('Musterstr. 1', $address->street);
        self::assertSame('Berlin', $address->city);
        self::assertSame('10115', $address->zip);
        self::assertSame('DE', $address->countryCode);
        self::assertNull($address->additionalStreet);
    }

    #[Test]
    public function it_accepts_an_optional_additional_street(): void
    {
        $address = new Address('Musterstr. 1', 'Berlin', '10115', 'DE', 'Haus 2');

        self::assertSame('Haus 2', $address->additionalStreet);
    }

    /** @return list<array{0: string}> */
    public static function invalidCountryCodes(): array
    {
        return [
            ['de'],           // lower-case
            ['D'],            // too short
            ['DEU'],          // ISO 3166-1 alpha-3 not accepted
            ['D1'],           // contains digit
            [''],             // empty
        ];
    }

    #[Test]
    #[DataProvider('invalidCountryCodes')]
    public function it_rejects_non_iso_3166_country_code(string $countryCode): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/Country code must be ISO 3166-1 alpha-2/');

        new Address('Musterstr. 1', 'Berlin', '10115', $countryCode);
    }

    /** @return list<array{0: string, 1: string, 2: string}> */
    public static function emptyRequiredFields(): array
    {
        return [
            ['', 'Berlin', '10115'],
            ['Musterstr. 1', '', '10115'],
            ['Musterstr. 1', 'Berlin', ''],
            ['  ', 'Berlin', '10115'],     // whitespace-only treated as empty
        ];
    }

    #[Test]
    #[DataProvider('emptyRequiredFields')]
    public function it_rejects_empty_required_fields(string $street, string $city, string $zip): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/must be a non-empty string/');

        new Address($street, $city, $zip, 'DE');
    }
}
