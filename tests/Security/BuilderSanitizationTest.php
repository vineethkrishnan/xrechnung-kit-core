<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Security;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Builder\XRechnungBuilder;
use XrechnungKit\Mapping\Address;
use XrechnungKit\Mapping\DocumentMeta;
use XrechnungKit\Mapping\DocumentTotals;
use XrechnungKit\Mapping\LineItem;
use XrechnungKit\Mapping\MappingData;
use XrechnungKit\Mapping\Money;
use XrechnungKit\Mapping\Party;
use XrechnungKit\Mapping\PaymentMeans;
use XrechnungKit\Mapping\TaxBreakdown;
use XrechnungKit\XRechnungGenerator;
use XrechnungKit\XRechnungInvoiceTypeCode;
use XrechnungKit\XRechnungTaxCategory;
use XrechnungKit\XRechnungValidator;

/**
 * Defense-in-depth coverage for the MappingData -> Builder -> Generator path.
 *
 * The lifted Generator does string substitution (not DOM-based emission), so
 * if a consumer passes hostile content (XML special chars, control bytes, ...)
 * the raw bytes would land directly in the template and produce malformed
 * XML. The Builder applies sanitize() to every string field flowing into the
 * entity; these tests pin that behaviour.
 */
final class BuilderSanitizationTest extends TestCase
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
            $invalid = preg_replace('/\.xml$/', '_invalid.xml', $path);
            if (\is_string($invalid) && file_exists($invalid)) {
                @unlink($invalid);
            }
        }
        $this->cleanupPaths = [];
    }

    #[Test]
    public function it_escapes_xml_specials_in_string_fields_so_output_stays_well_formed(): void
    {
        $mapping = $this->buildMappingWithName('Anna & Bernd <Beratung> "GmbH"');
        $entity = XRechnungBuilder::buildEntity($mapping);

        $contents = $this->generateAndRead($entity);

        self::assertStringNotContainsString('Anna & Bernd <Beratung> "GmbH"', $contents, 'Raw special chars must not survive into the output');
        self::assertStringContainsString('Anna &amp; Bernd &lt;Beratung&gt; &quot;GmbH&quot;', $contents, 'Special chars must be XML-escaped');

        $lastPath = end($this->cleanupPaths);
        $validator = new XRechnungValidator();
        self::assertTrue(
            $validator->validate($lastPath === false ? '' : $lastPath),
            "Output with escaped specials must still pass XSD validation. Errors:\n  - "
            . implode("\n  - ", $validator->getErrors()),
        );
    }

    #[Test]
    public function it_strips_xml_1_0_forbidden_control_chars_from_fields(): void
    {
        $hostileName = "Beispiel\x01GmbH\x07\x1F";
        $mapping = $this->buildMappingWithName($hostileName);
        $entity = XRechnungBuilder::buildEntity($mapping);

        $contents = $this->generateAndRead($entity);

        self::assertStringContainsString('BeispielGmbH', $contents, 'Visible characters must survive sanitisation');
        self::assertStringNotContainsString("\x01", $contents);
        self::assertStringNotContainsString("\x07", $contents);
        self::assertStringNotContainsString("\x1F", $contents);
    }

    #[Test]
    public function it_preserves_visible_unicode_through_sanitisation(): void
    {
        $unicodeName = 'Beispielgesellschaft mit beschr\u{00E4}nkter Haftung';
        $mapping = $this->buildMappingWithName($unicodeName);
        $entity = XRechnungBuilder::buildEntity($mapping);

        $contents = $this->generateAndRead($entity);

        self::assertStringContainsString($unicodeName, $contents);
    }

    #[Test]
    public function it_keeps_quarantine_safety_net_for_direct_entity_construction_with_unsafe_input(): void
    {
        $entity = (new \XrechnungKit\XRechnungEntity())
            ->setInvoiceType('invoice')
            ->setInvoiceNumber('RE-INJECTION-001')
            ->setInvoiceDate('2026-05-09')
            ->setTypeCode(380)
            ->setCustomerNumber('K-1')
            ->setNote('Direct entity bypass test')
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
            ->setSupplierCompanyName('Beispiel <script>alert(1)</script> GmbH')
            ->setSupplierStreet('Lieferantenstr. 1')
            ->setSupplierCity('Berlin')
            ->setSupplierZip('10115')
            ->setSupplierCountryCode('DE')
            ->setSupplierCompanyId('HRB 12345')
            ->setSupplierVat('DE123456789')
            ->setSupplierName('Anna')
            ->setSupplierPhone('+49 30 12345678')
            ->setBuyerMail('einkauf@example-buyer.de')
            ->setBuyerNumber('K-987654')
            ->setBuyerStreet('Behoerdenweg 7')
            ->setBuyerAdditionalStreet('')
            ->setBuyerCity('Bonn')
            ->setBuyerZip('53113')
            ->setBuyerCountryCode('DE')
            ->setBuyerCompanyName('Bundesamt')
            ->setBuyerName('Bernd')
            ->setBuyerPhone('+49 228 87654321')
            ->setBuyerEmail('bernd@example-buyer.de');

        $entity->addLineItem(
            (new \XrechnungKit\XRechnungInvoiceLineItem())
                ->setItemNumber('1')
                ->setItemQuantity(1)
                ->setItemPrice(100.00)
                ->setItemUnitPrice(100.00)
                ->setItemDescription('X')
                ->setItemResource('X')
                ->setItemTaxCategory('S')
                ->setItemTax(19)
                ->setItemTaxScheme('VAT')
                ->setItemAllowanceCharge(0)
                ->setItemStartDate('2026-05-01')
                ->setItemEndDate('2026-05-09')
        );

        $path = sys_get_temp_dir() . '/xrechnung-kit-injection-' . uniqid('', true) . '.xml';
        $this->cleanupPaths[] = $path;

        $generator = new XRechnungGenerator($entity);
        $finalPath = $generator->generateXRechnung($path);

        $invalidPath = preg_replace('/\.xml$/', '_invalid.xml', $path);
        self::assertNotNull($invalidPath, 'preg_replace must succeed on the simple suffix swap');
        self::assertSame($invalidPath, $finalPath, 'Unsanitised injection input should land in quarantine, not at the target name');
        self::assertFileExists($invalidPath);
        self::assertFileDoesNotExist($path);
    }

    private function buildMappingWithName(string $sellerName): MappingData
    {
        return MappingData::standardInvoice(
            meta: new DocumentMeta(
                invoiceNumber: 'RE-SEC-001',
                type: XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE,
                issueDate: new \DateTimeImmutable('2026-05-09'),
                currency: 'EUR',
                buyerReference: '04011000-12345-67',
            ),
            seller: Party::business(
                name: $sellerName,
                address: new Address('Lieferantenstr. 1', 'Berlin', '10115', 'DE'),
            ),
            buyer: Party::publicAdministration(
                name: 'Bundesamt fuer Beispielzwecke',
                address: new Address('Behoerdenweg 7', 'Bonn', '53113', 'DE'),
                leitwegId: '04011000-12345-67',
            ),
            lines: [
                new LineItem(
                    id: '1',
                    description: 'Beratungsleistung',
                    quantity: '1',
                    unitCode: 'HUR',
                    unitPrice: Money::eur('100.00'),
                    lineTotal: Money::eur('100.00'),
                    taxCategory: XRechnungTaxCategory::STANDARD_RATE,
                    taxPercent: '19',
                ),
            ],
            taxes: [
                new TaxBreakdown(
                    category: XRechnungTaxCategory::STANDARD_RATE,
                    percent: '19',
                    taxableAmount: Money::eur('100.00'),
                    taxAmount: Money::eur('19.00'),
                ),
            ],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: new DocumentTotals(
                lineNet: Money::eur('100.00'),
                taxableAmount: Money::eur('100.00'),
                taxAmount: Money::eur('19.00'),
                payable: Money::eur('119.00'),
            ),
        );
    }

    private function generateAndRead(\XrechnungKit\XRechnungEntity $entity): string
    {
        $path = sys_get_temp_dir() . '/xrechnung-kit-sec-' . uniqid('', true) . '.xml';
        $this->cleanupPaths[] = $path;

        $generator = new XRechnungGenerator($entity);
        $finalPath = $generator->generateXRechnung($path);

        return (string) file_get_contents($finalPath);
    }
}
