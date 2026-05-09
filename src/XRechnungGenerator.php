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
    private LoggerInterface $logger;
    private NotificationDispatcherInterface $notifications;

    private $xmlContent;

    public const DOCUMENT_TYPES_TO_SKIP_GENERATION = ['offer', 'order'];

    public function __construct(
        XRechnungEntity $xRechnungEntity,
        ?XRechnungValidator $validator = null,
        ?LoggerInterface $logger = null,
        ?NotificationDispatcherInterface $notifications = null
    ) {
        $this->xRechnungEntity = $xRechnungEntity;
        if (!$validator) {
            $validator = new XRechnungValidator();
        }
        $this->validator = $validator;
        $this->logger = $logger ?? new NullLogger();
        $this->notifications = $notifications ?? new ChannelDispatcher();
    }

    /**
     * Generates an XRechnung XML file with invoice data.
     *
     * @param string $fileName The full path of the XML file to be written.
     * @return string The path of the generated XML file.
     */
    public function generateXRechnung(string $fileName = 'XRechnung.xml'): string
    {

        $this->xmlContent = XRechnungTemplate::getTemplate($this->xRechnungEntity->getInvoiceType());

        // Replace placeholders with actual data from XRechnungEntity
        $this->generateSummary()
            ->addCautionDepositReference()
            ->addSupplierParty()
            ->addBuyerParty()
            ->addInvoiceLineItem()
            ->clean();

        // the filename contains the complete path, so we need to create the directory if it does not exist when putting the file
        $directory = dirname($fileName);
        if (!file_exists($directory)) {
            @mkdir($directory, 0755, true);
        }
        file_put_contents($fileName, $this->xmlContent);

        if ($this->xRechnungEntity->getInvoiceType() == 'cancel') { // for cancel we need to return the file name
            return $fileName;
        }

        // Validate the XML against the XSD schema
        if (!$this->validator->validate($fileName)) {
            $errors = $this->validator->getErrors();
            $body = "An invalid XRechnung XML file was generated and stored for inspection.\n";
            $body .= "File: {$fileName}\n";
            $body .= "Errors:\n";
            foreach ($errors as $error) {
                $body .= "- $error\n";
            }
            $this->logger->info($body, ['scope' => 'xrechnung', 'file' => $fileName]);
            $this->notifications->dispatch(new Notification(
                title: 'XRechnung validation failed',
                body: $body,
                severity: Severity::Error,
                context: [
                    'file' => $fileName,
                    'invoiceNumber' => $this->xRechnungEntity->getInvoiceNumber(),
                    'errors' => $errors,
                ],
            ));
        }

        return $fileName;
    }

    /**
     * Fills the XML template with invoice data from XRechnungEntity.
     *
     * @return self The instance with the populated XML content.
     */
    private function generateSummary(): XRechnungGenerator
    {

        $amountPrefix = '';
        if ($this->xRechnungEntity->getInvoiceType() == 'cancel') {
            $amountPrefix = '-';
        }
        $this->xmlContent = str_replace('{INVOICE_NUMBER}', $this->xRechnungEntity->getInvoiceNumber(), $this->xmlContent);
        $this->xmlContent = str_replace('{RELATED_INVOICE_NUMBER}', $this->xRechnungEntity->getRelatedInvoiceNumber(), $this->xmlContent);
        $this->xmlContent = str_replace('{INVOICE_DATE}', $this->xRechnungEntity->getInvoiceDate(), $this->xmlContent);
        $this->xmlContent = str_replace('{INVOICE_TYPE_CODE}', $this->xRechnungEntity->getTypeCode(), $this->xmlContent);
        $this->xmlContent = str_replace('{CUSTOMER_NUMBER}', $this->xRechnungEntity->getCustomerNumber(), $this->xmlContent);
        $this->xmlContent = str_replace('{NOTE}', $this->xRechnungEntity->getNote(), $this->xmlContent);
        $this->xmlContent = str_replace('{CURRENCY_CODE}', $this->xRechnungEntity->getCurrencyCode(), $this->xmlContent);
        $this->xmlContent = str_replace('{BUYER_REFERENCE_NUMBER}', $this->xRechnungEntity->getBuyerReferenceNumber(), $this->xmlContent);
        $this->xmlContent = str_replace('{PAYMENT_CODE}', $this->xRechnungEntity->getPaymentCode(), $this->xmlContent);
        $this->xmlContent = str_replace('{FINANCIAL_NUMBER}', $this->xRechnungEntity->getFinancialNumber(), $this->xmlContent);
        $this->xmlContent = str_replace('{PAYMENT_NOTE}', $this->xRechnungEntity->getPaymentNote(), $this->xmlContent);
        $this->xmlContent = str_replace('{TAX_CATEGORY}', $this->xRechnungEntity->getTaxCategory(), $this->xmlContent);
        $this->xmlContent = str_replace('{AMOUNT_PREFIX}', $amountPrefix, $this->xmlContent);
        $this->xmlContent = str_replace('{TAX_AMOUNT}', $amountPrefix == '-' && $this->xRechnungEntity->getTaxAmount() < 0 ? number_format(abs($this->xRechnungEntity->getTaxAmount()), 2, '.', '') : number_format($this->xRechnungEntity->getTaxAmount(), 2, '.', ''), $this->xmlContent);
        $this->xmlContent = str_replace('{TAX}', $this->xRechnungEntity->getTax(), $this->xmlContent);
        $this->xmlContent = str_replace('{TAX_SCHEME}', $this->xRechnungEntity->getTaxScheme(), $this->xmlContent);
        $this->xmlContent = str_replace('{NET_AMOUNT}', $amountPrefix == '-' && $this->xRechnungEntity->getNetAmount() < 0 ? number_format(abs($this->xRechnungEntity->getNetAmount()), 2, '.', '') : number_format($this->xRechnungEntity->getNetAmount(), 2, '.', ''), $this->xmlContent);
        $this->xmlContent = str_replace('{GROSS_AMOUNT}', $amountPrefix == '-' && $this->xRechnungEntity->getGrossAmount() < 0 ? number_format(abs($this->xRechnungEntity->getGrossAmount()), 2, '.', '') : number_format($this->xRechnungEntity->getGrossAmount(), 2, '.', ''), $this->xmlContent);
        $this->xmlContent = str_replace('{DOWN_PAYMENT}', $amountPrefix == '-' && $this->xRechnungEntity->getDownPayment() < 0 ? number_format(abs($this->xRechnungEntity->getDownPayment()), 2, '.', '') : number_format($this->xRechnungEntity->getDownPayment(), 2, '.', ''), $this->xmlContent);
        $this->xmlContent = str_replace('{PAYABLE_AMOUNT}', $amountPrefix == '-' && $this->xRechnungEntity->getPayableAmount() < 0 ? number_format(abs($this->xRechnungEntity->getPayableAmount()), 2, '.', '') : number_format($this->xRechnungEntity->getPayableAmount(), 2, '.', ''), $this->xmlContent);
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
                $cautionReferenceTemplate .= str_replace('{REFERENCE_NUMBER}', $item['invoice_number_prefix'] . $item['invoice_number'], XRechnungTemplate::getCautionDepositEntityTemplate());
            }
        }

        $depositReferenceTemplate = '';
        if ($this->xRechnungEntity->getDepositDocuments()) {
            foreach ($this->xRechnungEntity->getDepositDocuments() as $item) {
                $depositReferenceTemplate .= str_replace('{REFERENCE_NUMBER}', $item['invoice_number_prefix'] . $item['invoice_number'], XRechnungTemplate::getCautionDepositEntityTemplate());
            }
        }

        // concatenate the caution and deposit entities to the xml content
        $this->xmlContent = str_replace('{CAUTION_DEPOSIT_REFERENCE}', $cautionReferenceTemplate . $depositReferenceTemplate, $this->xmlContent);

        return $this;
    }

    /**
     * Adds the supplier party information to the XRechnung XML content.
     *
     * @return XRechnungGenerator The current instance of XRechnungGenerator for method chaining.
     */
    private function addSupplierParty(): XRechnungGenerator
    {

        $supplierParty = XRechnungTemplate::getSupplierPartyTemplate();
        $supplierParty = str_replace('{EMAIL}', $this->xRechnungEntity->getSupplierEmail(), $supplierParty);
        $supplierParty = str_replace('{COMPANY_NAME}', $this->xRechnungEntity->getSupplierCompanyName(), $supplierParty);
        $supplierParty = str_replace('{STREET}', $this->xRechnungEntity->getSupplierStreet(), $supplierParty);
        $supplierParty = str_replace('{CITY}', $this->xRechnungEntity->getSupplierCity(), $supplierParty);
        $supplierParty = str_replace('{ZIP}', $this->xRechnungEntity->getSupplierZip(), $supplierParty);
        $supplierParty = str_replace('{COUNTRY_CODE}', $this->xRechnungEntity->getSupplierCountryCode(), $supplierParty);
        $supplierParty = str_replace('{COMPANY_ID}', $this->xRechnungEntity->getSupplierCompanyId(), $supplierParty);
        $supplierParty = str_replace('{VAT}', $this->xRechnungEntity->getSupplierVat(), $supplierParty);
        $supplierParty = str_replace('{COMPANY_NAME}', $this->xRechnungEntity->getSupplierCompanyName(), $supplierParty);
        $supplierParty = str_replace('{NAME}', $this->xRechnungEntity->getSupplierName(), $supplierParty);
        $supplierParty = str_replace('{PHONE}', $this->xRechnungEntity->getSupplierPhone(), $supplierParty);
        $supplierParty = str_replace('{EMAIL}', $this->xRechnungEntity->getSupplierEmail(), $supplierParty);

        $this->xmlContent = str_replace('{SUPPLIER_PARTY}', $supplierParty, $this->xmlContent);

        return $this;
    }

    /**
     * Adds buyer party details to the XML content from XRechnungEntity object.
     *
     * @return XRechnungGenerator Returns the instance of XRechnungGenerator.
     */
    public function addBuyerParty(): XRechnungGenerator
    {
        $buyerParty = XRechnungTemplate::getBuyerPartyTemplate();
        $buyerParty = str_replace('{MAIL}', $this->xRechnungEntity->getBuyerMail(), $buyerParty);
        $buyerParty = str_replace('{NUMBER}', $this->xRechnungEntity->getBuyerNumber(), $buyerParty);
        $buyerParty = str_replace('{STREET}', $this->xRechnungEntity->getBuyerStreet(), $buyerParty);
        $buyerParty = str_replace('{ADDITIONAL_STREET}', $this->xRechnungEntity->getBuyerAdditionalStreet(), $buyerParty);
        $buyerParty = str_replace('{CITY}', $this->xRechnungEntity->getBuyerCity(), $buyerParty);
        $buyerParty = str_replace('{ZIP}', $this->xRechnungEntity->getBuyerZip(), $buyerParty);
        $buyerParty = str_replace('{COUNTRY_CODE}', $this->xRechnungEntity->getBuyerCountryCode(), $buyerParty);
        $buyerParty = str_replace('{COMPANY_NAME}', $this->xRechnungEntity->getBuyerCompanyName(), $buyerParty);
        $buyerParty = str_replace('{NAME}', $this->xRechnungEntity->getBuyerName(), $buyerParty);
        $buyerParty = str_replace('{PHONE}', $this->xRechnungEntity->getBuyerPhone(), $buyerParty);
        $buyerParty = str_replace('{EMAIL}', $this->xRechnungEntity->getBuyerEmail(), $buyerParty);

        $this->xmlContent = str_replace('{BUYER_PARTY}', $buyerParty, $this->xmlContent);

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
                $allowanceChargeTemplate = $this->getAllowanceChargeTemplate();

                $invoiceLine = str_replace('{ITEM_NUMBER}', $lineItem->getItemNumber(), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_QUANTITY}', $lineItem->getItemQuantity(), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_PRICE}', number_format($lineItem->getItemPrice(), 2, '.', ''), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_START_DATE}', $lineItem->getItemStartDate(), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_END_DATE}', $lineItem->getItemEndDate(), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_DESCRIPTION}', $lineItem->getItemDescription(), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_RESOURCE}', $lineItem->getItemResource(), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_TAX_CATEGORY}', $lineItem->getItemTaxCategory(), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_TAX}', $lineItem->getItemTax(), $invoiceLine);
                $invoiceLine = str_replace('{ITEM_TAX_SCHEME}', $lineItem->getItemTaxScheme(), $invoiceLine);

                $unitPrice = number_format($lineItem->getItemUnitPrice(), 2, '.', '');
                $allowanceCharge = '';
                if ($lineItem->getItemAllowanceCharge() != 0) {
                    $unitPrice = $lineItem->getItemUnitPrice();
                }

                $invoiceLine = str_replace('{ITEM_UNIT_PRICE}', $unitPrice, $invoiceLine);
                $invoiceLine = str_replace('{ITEM_ALLOWANCE_CHARGE}', $allowanceCharge, $invoiceLine);

                $invoiceLineItems .= $invoiceLine;
            }
        }

        $this->xmlContent = str_replace('{INVOICE_LINE_ITEMS}', $invoiceLineItems, $this->xmlContent);

        return $this;
    }

    private function getLineItemTemplate(): string
    {
        if ($this->xRechnungEntity->getInvoiceType() == 'invoice') {
            return XRechnungTemplate::getInvoiceLineTemplate();
        }

        if ($this->xRechnungEntity->getInvoiceType() == 'caution' || $this->xRechnungEntity->getInvoiceType() == 'deposit') {
            return XRechnungTemplate::getCautionDepositLineTemplate();
        }

        if ($this->xRechnungEntity->getInvoiceType() == 'cancel') {
            return XRechnungTemplate::getCreditLineTemplate();
        }
        return '';
    }

    private function getAllowanceChargeTemplate(): string
    {
        return XRechnungTemplate::getAllowanceChargeTemplate();
    }

    /**
     * The clean function will remove all empty tags from the ERechnung XML content.
     */
    private function clean(): void
    {
        $this->xmlContent = preg_replace(
            '/<(\w+:)?\w+[^>]*>\s*<\/\1?\w+>/',
            '',
            $this->xmlContent
        );
    }
}
