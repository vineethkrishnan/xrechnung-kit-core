<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Smoke;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\XRechnungEntity;
use XrechnungKit\XRechnungGenerator;
use XrechnungKit\XRechnungInvoiceLineItem;
use XrechnungKit\XRechnungValidator;

final class GeneratorSmokeTest extends TestCase
{
    private string $outputPath;

    #[\Override]
    protected function setUp(): void
    {
        $this->outputPath = sys_get_temp_dir() . '/xrechnung-kit-smoke-' . uniqid('', true) . '.xml';
    }

    #[\Override]
    protected function tearDown(): void
    {
        $invalidPath = preg_replace('/\.xml$/', '_invalid.xml', $this->outputPath);
        foreach ([$this->outputPath, $invalidPath] as $f) {
            if (\is_string($f) && file_exists($f)) {
                @unlink($f);
            }
        }
    }

    #[Test]
    public function it_constructs_and_runs_the_pipeline_without_throwing(): void
    {
        $entity = $this->buildMinimalInvoiceEntity();
        $generator = new XRechnungGenerator($entity);

        $path = $generator->generateXRechnung($this->outputPath);

        self::assertSame($this->outputPath, $path);
        self::assertFileExists($path);
        $contents = (string) file_get_contents($path);
        self::assertNotEmpty($contents);
        self::assertStringContainsString('<ubl:Invoice', $contents);
        self::assertStringContainsString('RE-2026-0001', $contents);
        self::assertStringNotContainsString('{INVOICE_NUMBER}', $contents);
    }

    #[Test]
    public function it_produces_an_xsd_valid_invoice_for_a_minimal_populated_entity(): void
    {
        $entity = $this->buildMinimalInvoiceEntity();
        $generator = new XRechnungGenerator($entity);

        $path = $generator->generateXRechnung($this->outputPath);

        $validator = new XRechnungValidator();
        $isValid = $validator->validate($path);

        self::assertTrue(
            $isValid,
            "Generated XML failed XSD validation. Errors:\n  - "
            . implode("\n  - ", $validator->getErrors())
        );
    }

    private function buildMinimalInvoiceEntity(): XRechnungEntity
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
            ->setItemTaxCategory('S')
            ->setItemTax(19)
            ->setItemTaxScheme('VAT')
            ->setItemAllowanceCharge(0);

        $entity = (new XRechnungEntity())
            ->setInvoiceType('invoice')
            ->setInvoiceNumber('RE-2026-0001')
            ->setInvoiceDate('2026-05-09')
            ->setTypeCode(380)
            ->setCustomerNumber('123456')
            ->setNote('Smoke test invoice for xrechnung-kit')
            ->setCurrencyCode('EUR')
            ->setBuyerReferenceNumber('04011000-12345-67')
            ->setPaymentCode(58)
            ->setFinancialNumber('DE12500105170648489890')
            ->setPaymentNote('Payable within 30 days net.')
            ->setTaxCategory('S')
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
