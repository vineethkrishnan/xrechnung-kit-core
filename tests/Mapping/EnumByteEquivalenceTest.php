<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Mapping;

use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\XRechnungEntity;
use XrechnungKit\XRechnungGenerator;
use XrechnungKit\XRechnungInvoiceLineItem;
use XrechnungKit\XRechnungInvoiceTypeCode;
use XrechnungKit\XRechnungTaxCategory;

/**
 * Pins down that passing an enum case to setTypeCode / setTaxCategory produces
 * XML that is byte-identical to passing the raw scalar value. This is the
 * contract the A2 commit claimed; without it, callers using the new enum API
 * would silently get empty <cbc:InvoiceTypeCode/> elements.
 */
final class EnumByteEquivalenceTest extends TestCase
{
    private string $rawPath;
    private string $enumPath;

    #[Override]
    protected function setUp(): void
    {
        $this->rawPath = sys_get_temp_dir() . '/xrechnung-kit-enum-raw-' . uniqid('', true) . '.xml';
        $this->enumPath = sys_get_temp_dir() . '/xrechnung-kit-enum-cs-' . uniqid('', true) . '.xml';
    }

    #[Override]
    protected function tearDown(): void
    {
        foreach ([$this->rawPath, $this->enumPath] as $f) {
            if (file_exists($f)) {
                @unlink($f);
            }
        }
    }

    #[Test]
    public function it_produces_byte_identical_output_for_enum_case_vs_raw_scalar(): void
    {
        $rawEntity = $this->buildEntity(380, 'S');
        $enumEntity = $this->buildEntity(
            XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE,
            XRechnungTaxCategory::STANDARD_RATE,
        );

        (new XRechnungGenerator($rawEntity))->generateXRechnung($this->rawPath);
        (new XRechnungGenerator($enumEntity))->generateXRechnung($this->enumPath);

        self::assertFileExists($this->rawPath);
        self::assertFileExists($this->enumPath);

        self::assertSame(
            file_get_contents($this->rawPath),
            file_get_contents($this->enumPath),
            'XML output must be byte-identical regardless of whether the caller passes the enum case or its underlying scalar value',
        );
    }

    private function buildEntity(int|XRechnungInvoiceTypeCode $typeCode, string|XRechnungTaxCategory $taxCategory): XRechnungEntity
    {
        $lineItem = (new XRechnungInvoiceLineItem())
            ->setItemNumber('1')
            ->setItemQuantity(1)
            ->setItemPrice(100.00)
            ->setItemUnitPrice(100.00)
            ->setItemStartDate('2026-05-01')
            ->setItemEndDate('2026-05-09')
            ->setItemDescription('Beratungsleistung')
            ->setItemResource('Consulting hour')
            ->setItemTaxCategory($taxCategory)
            ->setItemTax(19)
            ->setItemTaxScheme('VAT')
            ->setItemAllowanceCharge(0);

        $entity = (new XRechnungEntity())
            ->setInvoiceType('invoice')
            ->setInvoiceNumber('RE-ENUM-001')
            ->setInvoiceDate('2026-05-09')
            ->setTypeCode($typeCode)
            ->setCustomerNumber('K-ENUM')
            ->setNote('Enum byte-equivalence test')
            ->setCurrencyCode('EUR')
            ->setBuyerReferenceNumber('04011000-12345-67')
            ->setPaymentCode(58)
            ->setFinancialNumber('DE12500105170648489890')
            ->setPaymentNote('Net 30')
            ->setTaxCategory($taxCategory)
            ->setTax(19)
            ->setTaxScheme('VAT')
            ->setNetAmount(100.00)
            ->setTaxAmount(19.00)
            ->setGrossAmount(119.00)
            ->setDownPayment(0.00)
            ->setPayableAmount(119.00)
            ->setCautionDocuments([])
            ->setDepositDocuments([])
            ->setSupplierEmail('billing@example-supplier.de')
            ->setSupplierCompanyName('Beispiel Lieferant GmbH')
            ->setSupplierStreet('Lieferantenstr. 1')
            ->setSupplierCity('Berlin')
            ->setSupplierZip('10115')
            ->setSupplierCountryCode('DE')
            ->setSupplierCompanyId('HRB 12345')
            ->setSupplierVat('DE123456789')
            ->setSupplierName('Anna Beispiel')
            ->setSupplierPhone('+49 30 12345678')
            ->setBuyerMail('einkauf@example-buyer.de')
            ->setBuyerNumber('K-987654')
            ->setBuyerStreet('Behoerdenweg 7')
            ->setBuyerAdditionalStreet('Haus 2')
            ->setBuyerCity('Bonn')
            ->setBuyerZip('53113')
            ->setBuyerCountryCode('DE')
            ->setBuyerCompanyName('Bundesamt fuer Beispielzwecke')
            ->setBuyerName('Bernd Beispiel')
            ->setBuyerPhone('+49 228 87654321')
            ->setBuyerEmail('bernd.beispiel@example-buyer.de');

        $entity->addLineItem($lineItem);

        return $entity;
    }
}
