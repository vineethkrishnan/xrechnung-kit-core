<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Pipeline;

use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\AtomicWriter;
use XrechnungKit\XRechnungEntity;
use XrechnungKit\XRechnungGenerator;
use XrechnungKit\XRechnungInvoiceLineItem;
use XrechnungKit\XRechnungValidator;

/**
 * Pins down the A5 pipeline invariants:
 *
 * - Invalid XML never lands at the caller-supplied target name.
 * - The opposite-sibling file (valid vs *_invalid.xml) is always removed
 *   on a successful write so the name space is monotonic.
 * - The atomic write pattern uses temp + rename (no half-written file at
 *   the target name).
 */
final class QuarantineBehaviorTest extends TestCase
{
    private string $outputPath;
    private string $invalidPath;

    #[Override]
    protected function setUp(): void
    {
        $this->outputPath = sys_get_temp_dir() . '/xrechnung-kit-quarantine-' . uniqid('', true) . '.xml';
        $this->invalidPath = AtomicWriter::quarantinePath($this->outputPath);
    }

    #[Override]
    protected function tearDown(): void
    {
        foreach ([$this->outputPath, $this->invalidPath] as $f) {
            if (file_exists($f)) {
                @unlink($f);
            }
        }
    }

    #[Test]
    public function it_quarantines_to_sibling_when_validator_rejects(): void
    {
        $entity = $this->buildMinimalInvoiceEntity();
        $generator = new XRechnungGenerator($entity, $this->stubValidator(false));

        $finalPath = $generator->generateXRechnung($this->outputPath);

        self::assertSame($this->invalidPath, $finalPath);
        self::assertFileExists($this->invalidPath);
        self::assertFileDoesNotExist($this->outputPath);
    }

    #[Test]
    public function it_removes_quarantine_sibling_when_subsequent_write_is_valid(): void
    {
        $entity = $this->buildMinimalInvoiceEntity();

        $invalidGen = new XRechnungGenerator($entity, $this->stubValidator(false));
        $invalidGen->generateXRechnung($this->outputPath);
        self::assertFileExists($this->invalidPath);

        $validGen = new XRechnungGenerator($entity, $this->stubValidator(true));
        $finalPath = $validGen->generateXRechnung($this->outputPath);

        self::assertSame($this->outputPath, $finalPath);
        self::assertFileExists($this->outputPath);
        self::assertFileDoesNotExist($this->invalidPath);
    }

    #[Test]
    public function it_removes_valid_target_when_subsequent_write_is_invalid(): void
    {
        $entity = $this->buildMinimalInvoiceEntity();

        $validGen = new XRechnungGenerator($entity, $this->stubValidator(true));
        $validGen->generateXRechnung($this->outputPath);
        self::assertFileExists($this->outputPath);

        $invalidGen = new XRechnungGenerator($entity, $this->stubValidator(false));
        $finalPath = $invalidGen->generateXRechnung($this->outputPath);

        self::assertSame($this->invalidPath, $finalPath);
        self::assertFileExists($this->invalidPath);
        self::assertFileDoesNotExist($this->outputPath);
    }

    #[Test]
    public function it_does_not_leave_a_temp_file_alongside_a_successful_write(): void
    {
        $entity = $this->buildMinimalInvoiceEntity();
        $generator = new XRechnungGenerator($entity, $this->stubValidator(true));

        $generator->generateXRechnung($this->outputPath);

        $matches = glob(\dirname($this->outputPath) . DIRECTORY_SEPARATOR . '.xrechnung_kit_*.tmp');
        $stragglers = $matches === false ? [] : $matches;
        self::assertSame([], $stragglers, 'AtomicWriter must not leave a .tmp straggler in the target directory');
    }

    private function stubValidator(bool $verdict): XRechnungValidator
    {
        return new class ($verdict) extends XRechnungValidator {
            public function __construct(private readonly bool $verdict)
            {
                parent::__construct();
            }

            #[Override]
            public function validateContent(string $xml): bool
            {
                return $this->verdict;
            }

            #[Override]
            public function validate(string $xmlFile): bool
            {
                return $this->verdict;
            }

            #[Override]
            public function getErrors(): array
            {
                return $this->verdict ? [] : ['stub: forced invalid for test'];
            }
        };
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
            ->setInvoiceNumber('RE-QUARANTINE-001')
            ->setInvoiceDate('2026-05-09')
            ->setTypeCode(380)
            ->setCustomerNumber('K-1')
            ->setNote('Quarantine behaviour test')
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
