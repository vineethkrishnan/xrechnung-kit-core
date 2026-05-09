<?php

declare(strict_types=1);

namespace XrechnungKit\Tests\Builder;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use XrechnungKit\Builder\XRechnungBuilder;
use XrechnungKit\Mapping\Address;
use XrechnungKit\Mapping\BillingReference;
use XrechnungKit\Mapping\Contact;
use XrechnungKit\Mapping\DocumentMeta;
use XrechnungKit\Mapping\DocumentPeriod;
use XrechnungKit\Mapping\DocumentTotals;
use XrechnungKit\Mapping\LineItem;
use XrechnungKit\Mapping\MappingData;
use XrechnungKit\Mapping\Money;
use XrechnungKit\Mapping\Party;
use XrechnungKit\Mapping\PaymentMeans;
use XrechnungKit\Mapping\TaxBreakdown;
use XrechnungKit\Mapping\TaxId;
use XrechnungKit\XRechnungGenerator;
use XrechnungKit\XRechnungInvoiceTypeCode;
use XrechnungKit\XRechnungTaxCategory;
use XrechnungKit\XRechnungValidator;

/**
 * Verifies that MappingData -> XRechnungBuilder -> XRechnungEntity produces
 * the same downstream behaviour as the lifted entity-construction pattern
 * the smoke test exercises. The bridge is the v1.0 transition path: new
 * callers use MappingData and the named constructors; the existing
 * Generator pipeline stays unchanged behind the entity boundary.
 */
final class XRechnungBuilderTest extends TestCase
{
    /** @var list<string> */
    private array $cleanupPaths = [];

    #[Override]
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
    public function it_builds_an_entity_from_a_standard_invoice_mapping(): void
    {
        $mapping = $this->buildStandardInvoiceMapping();
        $entity = XRechnungBuilder::buildEntity($mapping);

        self::assertSame('invoice', $entity->getInvoiceType());
        self::assertSame(380, $entity->getTypeCode());
        self::assertSame('RE-2026-0001', $entity->getInvoiceNumber());
        self::assertSame('2026-05-09', $entity->getInvoiceDate());
        self::assertSame('EUR', $entity->getCurrencyCode());
        self::assertSame('480.00', $entity->getNetAmount());
        self::assertSame('91.20', $entity->getTaxAmount());
        self::assertSame('571.20', $entity->getGrossAmount());
        self::assertSame('571.20', $entity->getPayableAmount());
        self::assertSame('Beispiel Lieferant GmbH', $entity->getSupplierCompanyName());
        self::assertSame('DE123456789', $entity->getSupplierVat());
        self::assertSame('Bundesamt fuer XYZ', $entity->getBuyerCompanyName());
        self::assertSame('04011000-12345-67', $entity->getBuyerNumber());
        self::assertCount(1, $entity->getLineItems());
    }

    #[Test]
    public function it_picks_cancel_template_for_a_credit_note_mapping(): void
    {
        $mapping = MappingData::creditNote(
            meta: new DocumentMeta(
                invoiceNumber: 'STORNO-001',
                type: XRechnungInvoiceTypeCode::DEBIT_NOTE,
                issueDate: new DateTimeImmutable('2026-05-09'),
                currency: 'EUR',
            ),
            seller: $this->seller(),
            buyer: $this->publicBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            prior: new BillingReference('RE-2026-0001', new DateTimeImmutable('2026-04-01')),
        );

        $entity = XRechnungBuilder::buildEntity($mapping);

        self::assertSame('cancel', $entity->getInvoiceType());
        self::assertSame(381, $entity->getTypeCode());
        self::assertSame('RE-2026-0001', $entity->getRelatedInvoiceNumber());
    }

    #[Test]
    public function it_picks_caution_template_for_a_partial_invoice_mapping(): void
    {
        $mapping = MappingData::partialInvoice(
            meta: new DocumentMeta(
                invoiceNumber: 'ANZ-2026-001',
                type: XRechnungInvoiceTypeCode::CREDIT_NOTE,
                issueDate: new DateTimeImmutable('2026-05-09'),
                currency: 'EUR',
            ),
            seller: $this->seller(),
            buyer: $this->publicBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            period: new DocumentPeriod(
                new DateTimeImmutable('2026-05-01'),
                new DateTimeImmutable('2026-05-31'),
            ),
        );

        $entity = XRechnungBuilder::buildEntity($mapping);

        self::assertSame('caution', $entity->getInvoiceType(), 'Partial invoices flow through the caution template per the L3 deposit alias');
        self::assertSame(326, $entity->getTypeCode());
        self::assertSame('ANZ-2026-001', $entity->getInvoiceNumber());
    }

    #[Test]
    public function it_builds_a_deposit_cancellation_with_both_prior_reference_and_cancel_template(): void
    {
        $mapping = MappingData::depositCancellation(
            meta: new DocumentMeta(
                invoiceNumber: 'STORNO-DEP-001',
                type: XRechnungInvoiceTypeCode::DEBIT_NOTE,
                issueDate: new DateTimeImmutable('2026-05-09'),
                currency: 'EUR',
            ),
            seller: $this->seller(),
            buyer: $this->publicBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            prior: new BillingReference('ANZ-2026-001', new DateTimeImmutable('2026-04-01')),
            period: new DocumentPeriod(
                new DateTimeImmutable('2026-04-01'),
                new DateTimeImmutable('2026-04-30'),
            ),
        );

        $entity = XRechnungBuilder::buildEntity($mapping);

        self::assertSame('cancel', $entity->getInvoiceType(), 'Deposit cancellations flow through the cancel template');
        self::assertSame(381, $entity->getTypeCode());
        self::assertSame('ANZ-2026-001', $entity->getRelatedInvoiceNumber(), 'Prior reference must surface as RelatedInvoiceNumber on the lifted entity');
    }

    #[Test]
    public function it_picks_caution_template_for_a_caution_invoice_mapping(): void
    {
        $mapping = MappingData::cautionInvoice(
            meta: new DocumentMeta(
                invoiceNumber: 'CAUTION-001',
                type: XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE,
                issueDate: new DateTimeImmutable('2026-05-09'),
                currency: 'EUR',
                dueDate: new DateTimeImmutable('2026-06-01'),
            ),
            seller: $this->seller(),
            buyer: $this->publicBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890')],
            totals: $this->totals(),
            period: new DocumentPeriod(
                new DateTimeImmutable('2026-05-01'),
                new DateTimeImmutable('2026-05-31'),
            ),
        );

        $entity = XRechnungBuilder::buildEntity($mapping);

        self::assertSame('caution', $entity->getInvoiceType());
    }

    #[Test]
    public function it_produces_xsd_valid_output_when_run_through_the_generator_pipeline(): void
    {
        $mapping = $this->buildStandardInvoiceMapping();
        $entity = XRechnungBuilder::buildEntity($mapping);

        $path = sys_get_temp_dir() . '/xrechnung-kit-builder-' . uniqid('', true) . '.xml';
        $this->cleanupPaths[] = $path;

        $generator = new XRechnungGenerator($entity);
        $finalPath = $generator->generateXRechnung($path);

        self::assertSame($path, $finalPath, 'MappingData-built entity should produce a valid file at the target name');

        $validator = new XRechnungValidator();
        self::assertTrue(
            $validator->validate($finalPath),
            "Generated XML failed XSD validation. Errors:\n  - " . implode("\n  - ", $validator->getErrors()),
        );
    }

    private function buildStandardInvoiceMapping(): MappingData
    {
        return MappingData::standardInvoice(
            meta: new DocumentMeta(
                invoiceNumber: 'RE-2026-0001',
                type: XRechnungInvoiceTypeCode::COMMERCIAL_INVOICE,
                issueDate: new DateTimeImmutable('2026-05-09'),
                currency: 'EUR',
                buyerReference: '04011000-12345-67',
                note: 'Builder smoke test',
            ),
            seller: $this->seller(),
            buyer: $this->publicBuyer(),
            lines: [$this->line()],
            taxes: [$this->tax()],
            payment: [PaymentMeans::sepaCreditTransfer('DE12500105170648489890', 'INGDDEFFXXX', 'Beispiel Lieferant GmbH', 'RE-2026-0001')],
            totals: $this->totals(),
        );
    }

    private function seller(): Party
    {
        return Party::business(
            name: 'Beispiel Lieferant GmbH',
            address: new Address('Lieferantenstr. 1', 'Berlin', '10115', 'DE'),
            taxId: TaxId::vatId('DE123456789'),
            contact: new Contact(name: 'Anna Beispiel', phone: '+49 30 12345678', email: 'billing@example-supplier.de'),
            endpointEmail: 'billing@example-supplier.de',
        );
    }

    private function publicBuyer(): Party
    {
        return Party::publicAdministration(
            name: 'Bundesamt fuer XYZ',
            address: new Address('Behoerdenweg 7', 'Bonn', '53113', 'DE', 'Haus 2'),
            leitwegId: '04011000-12345-67',
            contact: new Contact(name: 'Bernd Beispiel', phone: '+49 228 87654321', email: 'bernd.beispiel@example-buyer.de'),
            endpointEmail: 'einkauf@example-buyer.de',
        );
    }

    private function line(): LineItem
    {
        return new LineItem(
            id: '1',
            description: 'Beratungsleistung',
            quantity: '4',
            unitCode: 'HUR',
            unitPrice: Money::eur('120.00'),
            lineTotal: Money::eur('480.00'),
            taxCategory: XRechnungTaxCategory::STANDARD_RATE,
            taxPercent: '19.00',
            name: 'Consulting hour',
        );
    }

    private function tax(): TaxBreakdown
    {
        return new TaxBreakdown(
            category: XRechnungTaxCategory::STANDARD_RATE,
            percent: '19.00',
            taxableAmount: Money::eur('480.00'),
            taxAmount: Money::eur('91.20'),
        );
    }

    private function totals(): DocumentTotals
    {
        return new DocumentTotals(
            lineNet: Money::eur('480.00'),
            taxableAmount: Money::eur('480.00'),
            taxAmount: Money::eur('91.20'),
            payable: Money::eur('571.20'),
        );
    }
}
