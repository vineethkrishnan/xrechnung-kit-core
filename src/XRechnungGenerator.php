<?php

namespace XrechnungKit;

use XrechnungKit\Logger\LoggerInterface;
use XrechnungKit\Logger\NullLogger;
use XrechnungKit\Notification\ChannelDispatcher;
use XrechnungKit\Notification\Notification;
use XrechnungKit\Notification\NotificationDispatcherInterface;
use XrechnungKit\Notification\Severity;

/**
 * XRechnungGenerator is responsible for generating XRechnung XML files
 * using the data provided by the XRechnungEntity.
 */
class XRechnungGenerator
{
    private $xRechnungEntity;
    private $validator;
    private AtomicWriter $writer;
    private LoggerInterface $logger;
    private NotificationDispatcherInterface $notifications;

    private $xmlContent;

    public const DOCUMENT_TYPES_TO_SKIP_GENERATION = ['offer', 'order'];

    public function __construct(
        XRechnungEntity $xRechnungEntity,
        ?XRechnungValidator $validator = null,
        ?LoggerInterface $logger = null,
        ?NotificationDispatcherInterface $notifications = null,
        ?AtomicWriter $writer = null
    ) {
        $this->xRechnungEntity = $xRechnungEntity;
        $this->validator = $validator ?? new XRechnungValidator();
        $this->writer = $writer ?? new AtomicWriter();
        $this->logger = $logger ?? new NullLogger();
        $this->notifications = $notifications ?? new ChannelDispatcher();
    }

    /**
     * Builds the XRechnung XML in memory, validates it against the bundled
     * UBL XSD, then atomically lands it on disk:
     *
     * - Valid XML -> $fileName (and any pre-existing _invalid sibling is removed).
     * - Invalid XML -> the *_invalid.xml sibling (and any pre-existing valid
     *   file at $fileName is removed). The pipeline never presents invalid XML
     *   at the caller-supplied target name.
     *
     * 'cancel' invoice type writes to $fileName without validation, preserving
     * the L3 behaviour. A4 will revisit whether cancel docs should validate.
     *
     * @return string The path the XML was written to (either $fileName or its quarantine sibling).
     */
    public function generateXRechnung(string $fileName = 'XRechnung.xml'): string
    {
        $this->xmlContent = XRechnungTemplate::getTemplate($this->xRechnungEntity->getInvoiceType());

        $this->generateSummary()
            ->addCautionDepositReference()
            ->addSupplierParty()
            ->addBuyerParty()
            ->addInvoiceLineItem()
            ->clean();

        $type = $this->xRechnungEntity->getInvoiceType();
        if ($type == 'cancel') {
            return $this->writer->write($this->xmlContent, $fileName, true);
        }

        $isValid = $this->validator->validateContent($this->xmlContent);
        $finalPath = $this->writer->write($this->xmlContent, $fileName, $isValid);

        if (!$isValid) {
            $errors = $this->validator->getErrors();
            $body = "An invalid XRechnung XML file was generated and quarantined for inspection.\n";
            $body .= "File: {$finalPath}\n";
            $body .= "Errors:\n";
            foreach ($errors as $error) {
                $body .= "- $error\n";
            }
            $this->logger->info($body, ['scope' => 'xrechnung', 'file' => $finalPath]);
            $this->notifications->dispatch(new Notification(
                title: 'XRechnung validation failed',
                body: $body,
                severity: Severity::Error,
                context: [
                    'file' => $finalPath,
                    'invoiceNumber' => $this->xRechnungEntity->getInvoiceNumber(),
                    'errors' => $errors,
                ],
            ));
        }

        return $finalPath;
    }

    /**
     * Fills the XML template with invoice data from XRechnungEntity.
     *
     * @return self The instance with the populated XML content.
     */
    private function generateSummary(): XRechnungGenerator
    {
        $entity = $this->xRechnungEntity;
        $amountPrefix = $entity->getInvoiceType() == 'cancel' ? '-' : '';

        $signedAmount = static fn (mixed $value): string => $amountPrefix == '-' && (float) $value < 0
            ? number_format(abs((float) $value), 2, '.', '')
            : number_format((float) $value, 2, '.', '');

        $this->xmlContent = self::fill($this->xmlContent, '{INVOICE_NUMBER}', $entity->getInvoiceNumber());
        $this->xmlContent = self::fill($this->xmlContent, '{RELATED_INVOICE_NUMBER}', $entity->getRelatedInvoiceNumber());
        $this->xmlContent = self::fill($this->xmlContent, '{INVOICE_DATE}', $entity->getInvoiceDate());
        $this->xmlContent = self::fill($this->xmlContent, '{INVOICE_TYPE_CODE}', $entity->getTypeCode());
        $this->xmlContent = self::fill($this->xmlContent, '{CUSTOMER_NUMBER}', $entity->getCustomerNumber());
        $this->xmlContent = self::fill($this->xmlContent, '{NOTE}', $entity->getNote());
        $this->xmlContent = self::fill($this->xmlContent, '{CURRENCY_CODE}', $entity->getCurrencyCode());
        $this->xmlContent = self::fill($this->xmlContent, '{BUYER_REFERENCE_NUMBER}', $entity->getBuyerReferenceNumber());
        $this->xmlContent = self::fill($this->xmlContent, '{PAYMENT_CODE}', $entity->getPaymentCode());
        $this->xmlContent = self::fill($this->xmlContent, '{FINANCIAL_NUMBER}', $entity->getFinancialNumber());
        $this->xmlContent = self::fill($this->xmlContent, '{PAYMENT_NOTE}', $entity->getPaymentNote());
        $this->xmlContent = self::fill($this->xmlContent, '{TAX_CATEGORY}', $entity->getTaxCategory());
        $this->xmlContent = self::fill($this->xmlContent, '{AMOUNT_PREFIX}', $amountPrefix);
        $this->xmlContent = self::fill($this->xmlContent, '{TAX_AMOUNT}', $signedAmount($entity->getTaxAmount()));
        $this->xmlContent = self::fill($this->xmlContent, '{TAX}', $entity->getTax());
        $this->xmlContent = self::fill($this->xmlContent, '{TAX_SCHEME}', $entity->getTaxScheme());
        $this->xmlContent = self::fill($this->xmlContent, '{NET_AMOUNT}', $signedAmount($entity->getNetAmount()));
        $this->xmlContent = self::fill($this->xmlContent, '{GROSS_AMOUNT}', $signedAmount($entity->getGrossAmount()));
        $this->xmlContent = self::fill($this->xmlContent, '{DOWN_PAYMENT}', $signedAmount($entity->getDownPayment()));
        $this->xmlContent = self::fill($this->xmlContent, '{PAYABLE_AMOUNT}', $signedAmount($entity->getPayableAmount()));

        return $this;
    }

    /**
     * Adds caution deposit reference details to the XML content.
     *
     * @return XRechnungGenerator The current instance of XRechnungGenerator with updated XML content.
     */
    private function addCautionDepositReference(): XRechnungGenerator
    {
        $cautionReferenceTemplate = '';
        if ($this->xRechnungEntity->getCautionDocuments()) {
            foreach ($this->xRechnungEntity->getCautionDocuments() as $item) {
                $cautionReferenceTemplate .= self::fill(
                    XRechnungTemplate::getCautionDepositEntityTemplate(),
                    '{REFERENCE_NUMBER}',
                    ($item['invoice_number_prefix'] ?? '') . ($item['invoice_number'] ?? '')
                );
            }
        }

        $depositReferenceTemplate = '';
        if ($this->xRechnungEntity->getDepositDocuments()) {
            foreach ($this->xRechnungEntity->getDepositDocuments() as $item) {
                $depositReferenceTemplate .= self::fill(
                    XRechnungTemplate::getCautionDepositEntityTemplate(),
                    '{REFERENCE_NUMBER}',
                    ($item['invoice_number_prefix'] ?? '') . ($item['invoice_number'] ?? '')
                );
            }
        }

        $this->xmlContent = self::fill(
            $this->xmlContent,
            '{CAUTION_DEPOSIT_REFERENCE}',
            $cautionReferenceTemplate . $depositReferenceTemplate
        );

        return $this;
    }

    /**
     * Adds the supplier party information to the XRechnung XML content.
     *
     * @return XRechnungGenerator The current instance of XRechnungGenerator for method chaining.
     */
    private function addSupplierParty(): XRechnungGenerator
    {
        $entity = $this->xRechnungEntity;
        $supplierParty = XRechnungTemplate::getSupplierPartyTemplate();

        $supplierParty = self::fill($supplierParty, '{EMAIL}', $entity->getSupplierEmail());
        $supplierParty = self::fill($supplierParty, '{COMPANY_NAME}', $entity->getSupplierCompanyName());
        $supplierParty = self::fill($supplierParty, '{STREET}', $entity->getSupplierStreet());
        $supplierParty = self::fill($supplierParty, '{CITY}', $entity->getSupplierCity());
        $supplierParty = self::fill($supplierParty, '{ZIP}', $entity->getSupplierZip());
        $supplierParty = self::fill($supplierParty, '{COUNTRY_CODE}', $entity->getSupplierCountryCode());
        $supplierParty = self::fill($supplierParty, '{COMPANY_ID}', $entity->getSupplierCompanyId());
        $supplierParty = self::fill($supplierParty, '{VAT}', $entity->getSupplierVat());
        $supplierParty = self::fill($supplierParty, '{NAME}', $entity->getSupplierName());
        $supplierParty = self::fill($supplierParty, '{PHONE}', $entity->getSupplierPhone());

        $this->xmlContent = self::fill($this->xmlContent, '{SUPPLIER_PARTY}', $supplierParty);

        return $this;
    }

    /**
     * Adds buyer party details to the XML content from XRechnungEntity object.
     *
     * @return XRechnungGenerator Returns the instance of XRechnungGenerator.
     */
    public function addBuyerParty(): XRechnungGenerator
    {
        $entity = $this->xRechnungEntity;
        $buyerParty = XRechnungTemplate::getBuyerPartyTemplate();

        $buyerParty = self::fill($buyerParty, '{MAIL}', $entity->getBuyerMail());
        $buyerParty = self::fill($buyerParty, '{NUMBER}', $entity->getBuyerNumber());
        $buyerParty = self::fill($buyerParty, '{STREET}', $entity->getBuyerStreet());
        $buyerParty = self::fill($buyerParty, '{ADDITIONAL_STREET}', $entity->getBuyerAdditionalStreet());
        $buyerParty = self::fill($buyerParty, '{CITY}', $entity->getBuyerCity());
        $buyerParty = self::fill($buyerParty, '{ZIP}', $entity->getBuyerZip());
        $buyerParty = self::fill($buyerParty, '{COUNTRY_CODE}', $entity->getBuyerCountryCode());
        $buyerParty = self::fill($buyerParty, '{COMPANY_NAME}', $entity->getBuyerCompanyName());
        $buyerParty = self::fill($buyerParty, '{NAME}', $entity->getBuyerName());
        $buyerParty = self::fill($buyerParty, '{PHONE}', $entity->getBuyerPhone());
        $buyerParty = self::fill($buyerParty, '{EMAIL}', $entity->getBuyerEmail());

        $this->xmlContent = self::fill($this->xmlContent, '{BUYER_PARTY}', $buyerParty);

        return $this;
    }

    /**
     * Adds an invoice line item to the XML content from XRechnungEntity object.
     *
     * @return XRechnungGenerator Returns the instance of XRechnungGenerator.
     */
    public function addInvoiceLineItem(): XRechnungGenerator
    {
        $invoiceLineItems = '';

        if ($this->xRechnungEntity->getLineItems()) {
            /** @var XRechnungInvoiceLineItem $lineItem */
            foreach ($this->xRechnungEntity->getLineItems() as $lineItem) {
                $invoiceLine = $this->getLineItemTemplate();

                $invoiceLine = self::fill($invoiceLine, '{ITEM_NUMBER}', $lineItem->getItemNumber());
                $invoiceLine = self::fill($invoiceLine, '{ITEM_QUANTITY}', $lineItem->getItemQuantity());
                $invoiceLine = self::fill($invoiceLine, '{ITEM_PRICE}', number_format((float) $lineItem->getItemPrice(), 2, '.', ''));
                $invoiceLine = self::fill($invoiceLine, '{ITEM_START_DATE}', $lineItem->getItemStartDate());
                $invoiceLine = self::fill($invoiceLine, '{ITEM_END_DATE}', $lineItem->getItemEndDate());
                $invoiceLine = self::fill($invoiceLine, '{ITEM_DESCRIPTION}', $lineItem->getItemDescription());
                $invoiceLine = self::fill($invoiceLine, '{ITEM_RESOURCE}', $lineItem->getItemResource());
                $invoiceLine = self::fill($invoiceLine, '{ITEM_TAX_CATEGORY}', $lineItem->getItemTaxCategory());
                $invoiceLine = self::fill($invoiceLine, '{ITEM_TAX}', $lineItem->getItemTax());
                $invoiceLine = self::fill($invoiceLine, '{ITEM_TAX_SCHEME}', $lineItem->getItemTaxScheme());

                $unitPrice = number_format((float) $lineItem->getItemUnitPrice(), 2, '.', '');
                $allowanceCharge = '';
                if ($lineItem->getItemAllowanceCharge() != 0) {
                    $unitPrice = (string) $lineItem->getItemUnitPrice();
                }

                $invoiceLine = self::fill($invoiceLine, '{ITEM_UNIT_PRICE}', $unitPrice);
                $invoiceLine = self::fill($invoiceLine, '{ITEM_ALLOWANCE_CHARGE}', $allowanceCharge);

                $invoiceLineItems .= $invoiceLine;
            }
        }

        $this->xmlContent = self::fill($this->xmlContent, '{INVOICE_LINE_ITEMS}', $invoiceLineItems);

        return $this;
    }

    private function getLineItemTemplate(): string
    {
        $type = $this->xRechnungEntity->getInvoiceType();

        if ($type == 'invoice') {
            return XRechnungTemplate::getInvoiceLineTemplate();
        }
        if ($type == 'caution' || $type == 'deposit') {
            return XRechnungTemplate::getCautionDepositLineTemplate();
        }
        if ($type == 'cancel') {
            return XRechnungTemplate::getCreditLineTemplate();
        }
        return '';
    }

    /**
     * The clean function will remove all empty tags from the XRechnung XML content.
     */
    private function clean(): void
    {
        $this->xmlContent = preg_replace(
            '/<(\w+:)?\w+[^>]*>\s*<\/\1?\w+>/',
            '',
            $this->xmlContent
        );
    }

    /**
     * Substitute one placeholder in $haystack, coercing null to ''. PHP 8.4
     * deprecates passing null to str_replace's $replace; entity getters can
     * return null whenever a field is not set.
     */
    private static function fill(string $haystack, string $placeholder, mixed $value): string
    {
        return str_replace($placeholder, (string) ($value ?? ''), $haystack);
    }
}
