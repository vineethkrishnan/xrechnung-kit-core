<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Exception\MappingDataException;
use XrechnungKit\Mapping\TaxId;

final class TaxIdTest extends TestCase
{
    #[Test]
    public function it_builds_a_vat_id_via_named_constructor(): void
    {
        $taxId = TaxId::vatId('DE123456789');

        self::assertSame('DE123456789', $taxId->companyId);
        self::assertSame('VAT', $taxId->schemeId);
    }

    #[Test]
    public function it_builds_a_company_registration_via_named_constructor(): void
    {
        $taxId = TaxId::companyRegistration('HRB 12345');

        self::assertSame('HRB 12345', $taxId->companyId);
        self::assertSame('FC', $taxId->schemeId);
    }

    #[Test]
    public function it_rejects_an_empty_company_id(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/companyId/');

        TaxId::vatId('');
    }

    #[Test]
    public function it_rejects_an_empty_scheme_id(): void
    {
        $this->expectException(MappingDataException::class);
        $this->expectExceptionMessageMatches('/schemeId/');

        new TaxId('DE123', '');
    }
}
