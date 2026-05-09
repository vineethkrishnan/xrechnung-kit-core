<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\DocumentTypes;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\AtomicWriter;
use XrechnungKit\XRechnungEntity;
use XrechnungKit\XRechnungGenerator;
use XrechnungKit\XRechnungInvoiceLineItem;
use XrechnungKit\XRechnungValidator;

/**
 * Functional coverage of the three L3-shipped templates beyond the standard
 * invoice the smoke test already covers:
 *
 *   - 'caution'  -> XRechnungCautionTemplate.xml (security deposit / caution)
 *   - 'cancel'   -> XRechnungCancelTemplate.xml  (credit note 381 with
 *                                                 negated amounts via
 *                                                 {AMOUNT_PREFIX})
 *
 * Note: the L3 generator deliberately skips XSD validation for the 'cancel'
 * type and writes directly to the target name. We preserve that behaviour
 * here and verify only that the file lands at the target name with the
 * expected CreditNote root, rather than asserting XSD validity. A4 will
 * revisit whether cancel docs should validate uniformly.
 */
final class DocumentTypeCoverageTest extends TestCase
{
    /** @var list<string> */
    private array $cleanupPaths = [];

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->cleanupPaths as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
            $invalid = AtomicWriter::quarantinePath($path);
            if (file_exists($invalid)) {
                @unlink($invalid);
            }
        }
        $this->cleanupPaths = [];
    }

    #[Test]
    public function it_generates_a_caution_invoice_that_passes_xsd_validation(): void
    {
        $path = $this->tempPath('caution');
        $entity = $this->buildEntity('caution', 'CAUTION-RE-001', 380);

        $generator = new XRechnungGenerator($entity);
        $finalPath = $generator->generateXRechnung($path);

        self::assertSame($path, $finalPath, 'Caution invoice must land at the target name (not the quarantine sibling)');
        self::assertFileExists($finalPath);
        $contents = (string) file_get_contents($finalPath);
        self::assertStringContainsString('<ubl:Invoice', $contents);
        self::assertStringContainsString('CAUTION-RE-001', $contents);
        self::assertStringNotContainsString('{', $contents, 'No template placeholders should remain after substitution');

        $validator = new XRechnungValidator();
        self::assertTrue(
            $validator->validate($finalPath),
            "Caution invoice failed XSD validation. Errors:\n  - " . implode("\n  - ", $validator->getErrors()),
        );
    }

    #[Test]
    public function it_generates_a_cancel_credit_note_with_negated_amounts(): void
    {
        $path = $this->tempPath('cancel');
        $entity = $this->buildEntity('cancel', 'STORNO-RE-001', 381);
        $entity->setRelatedInvoiceNumber('RE-2026-0001');

        $generator = new XRechnungGenerator($entity);
        $finalPath = $generator->generateXRechnung($path);

        self::assertSame($path, $finalPath, 'Cancel docs skip validation per L3 behaviour and write directly to the target name');
        self::assertFileExists($finalPath);
        $contents = (string) file_get_contents($finalPath);
        self::assertStringContainsString('<CreditNote', $contents, 'Cancel template uses the UBL CreditNote root with default namespace (not the ubl: prefix)');
        self::assertStringContainsString('STORNO-RE-001', $contents);
        self::assertStringContainsString('RE-2026-0001', $contents, 'Related invoice number should appear in the credit note');
        self::assertStringContainsString('-100.00', $contents, 'Amounts should carry the negative prefix on cancel docs');
        self::assertStringNotContainsString('{', $contents, 'No template placeholders should remain after substitution');
    }

    private function tempPath(string $kind): string
    {
        $path = sys_get_temp_dir() . "/xrechnung-kit-{$kind}-" . uniqid('', true) . '.xml';
        $this->cleanupPaths[] = $path;
        return $path;
    }

    private function buildEntity(string $invoiceType, string $invoiceNumber, int $typeCode): XRechnungEntity
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
            ->setInvoiceType($invoiceType)
            ->setInvoiceNumber($invoiceNumber)
            ->setInvoiceDate('2026-05-09')
            ->setTypeCode($typeCode)
            ->setCustomerNumber('K-1')
            ->setNote("Functional coverage test: {$invoiceType}")
            ->setCurrencyCode('EUR')
            ->setBuyerReferenceNumber('04011000-12345-67')
            ->setPaymentCode(58)
            ->setFinancialNumber('DE12500105170648489890')
            ->setPaymentNote('Net 30')
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
