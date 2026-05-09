<?php

declare(strict_types=1);

namespace XrechnungKit;

final class XRechnungEntity
{
    private mixed $invoiceNumber = null;
    private mixed $invoiceDate = null;
    private mixed $typeCode = null;
    private mixed $customerNumber = null;
    private mixed $note = null;
    private mixed $currencyCode = null;
    private mixed $buyerReferenceNumber = null;
    private mixed $paymentCode = null;
    private mixed $financialNumber = null;
    private mixed $paymentNote = null;
    private mixed $taxCategory = null;
    private mixed $tax = null;
    private mixed $taxScheme = null;
    private mixed $netAmount = null;
    private mixed $taxAmount = null;
    private mixed $grossAmount = null;
    private mixed $downPayment = null;
    private mixed $payableAmount = null;
    /** @var array<int, array<string, mixed>> */
    private array $cautionDocuments = [];
    /** @var array<int, array<string, mixed>> */
    private array $depositDocuments = [];
    private mixed $supplierEmail = null;
    private mixed $supplierCompanyName = null;
    private mixed $supplierStreet = null;
    private mixed $supplierCity = null;
    private mixed $supplierZip = null;
    private mixed $supplierCountryCode = null;
    private mixed $supplierCompanyId = null;
    private mixed $supplierVat = null;
    private mixed $supplierName = null;
    private mixed $supplierPhone = null;
    private mixed $buyerMail = null;
    private mixed $buyerNumber = null;
    private mixed $buyerStreet = null;
    private mixed $buyerAdditionalStreet = null;
    private mixed $buyerCity = null;
    private mixed $buyerZip = null;
    private mixed $buyerCountryCode = null;
    private mixed $buyerCompanyName = null;
    private mixed $buyerName = null;
    private mixed $buyerPhone = null;
    private mixed $buyerEmail = null;

    /** @var list<XRechnungInvoiceLineItem> */
    private array $lineItems = [];

    private mixed $invoiceType = null;

    private mixed $relatedInvoiceNumber = null;

    public function getTypeCode(): mixed
    {
        return $this->typeCode;
    }

    public function setTypeCode(mixed $typeCode): self
    {
        $this->typeCode = $typeCode;
        return $this;
    }

    public function getNote(): mixed
    {
        return $this->note;
    }

    public function setNote(mixed $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getCurrencyCode(): mixed
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(mixed $currencyCode): self
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    public function getBuyerReferenceNumber(): mixed
    {
        return $this->buyerReferenceNumber;
    }

    public function setBuyerReferenceNumber(mixed $buyerReferenceNumber): self
    {
        $this->buyerReferenceNumber = $buyerReferenceNumber;
        return $this;
    }

    public function getPaymentCode(): mixed
    {
        return $this->paymentCode;
    }

    public function setPaymentCode(mixed $paymentCode): self
    {
        $this->paymentCode = $paymentCode;
        return $this;
    }

    public function getFinancialNumber(): mixed
    {
        return $this->financialNumber;
    }

    public function setFinancialNumber(mixed $financialNumber): self
    {
        $this->financialNumber = $financialNumber;
        return $this;
    }

    public function getPaymentNote(): mixed
    {
        return $this->paymentNote;
    }

    public function setPaymentNote(mixed $paymentNote): self
    {
        $this->paymentNote = $paymentNote;
        return $this;
    }

    public function getTaxCategory(): mixed
    {
        return $this->taxCategory;
    }

    public function setTaxCategory(mixed $taxCategory): self
    {
        $this->taxCategory = $taxCategory;
        return $this;
    }

    public function getTax(): mixed
    {
        return $this->tax;
    }

    public function setTax(mixed $tax): self
    {
        $this->tax = $tax;
        return $this;
    }

    public function getTaxScheme(): mixed
    {
        return $this->taxScheme;
    }

    public function setTaxScheme(mixed $taxScheme): self
    {
        $this->taxScheme = $taxScheme;
        return $this;
    }

    public function getDownPayment(): mixed
    {
        return $this->downPayment;
    }

    public function setDownPayment(mixed $downPayment): self
    {
        $this->downPayment = $downPayment;
        return $this;
    }

    public function getPayableAmount(): mixed
    {
        return $this->payableAmount;
    }

    public function setPayableAmount(mixed $payableAmount): self
    {
        $this->payableAmount = $payableAmount;
        return $this;
    }

    /** @return array<int, array<string, mixed>> */
    public function getCautionDocuments(): array
    {
        return $this->cautionDocuments;
    }

    /** @param array<int, array<string, mixed>> $cautionDocuments */
    public function setCautionDocuments(array $cautionDocuments = []): self
    {
        $this->cautionDocuments = $cautionDocuments;
        return $this;
    }

    /** @return array<int, array<string, mixed>> */
    public function getDepositDocuments(): array
    {
        return $this->depositDocuments;
    }

    /** @param array<int, array<string, mixed>> $depositDocuments */
    public function setDepositDocuments(array $depositDocuments = []): self
    {
        $this->depositDocuments = $depositDocuments;
        return $this;
    }

    public function getSupplierEmail(): mixed
    {
        return $this->supplierEmail;
    }

    public function setSupplierEmail(mixed $supplierEmail): self
    {
        $this->supplierEmail = $supplierEmail;
        return $this;
    }

    public function getSupplierCompanyName(): mixed
    {
        return $this->supplierCompanyName;
    }

    public function setSupplierCompanyName(mixed $supplierCompanyName): self
    {
        $this->supplierCompanyName = $supplierCompanyName;
        return $this;
    }

    public function getSupplierStreet(): mixed
    {
        return $this->supplierStreet;
    }

    public function setSupplierStreet(mixed $supplierStreet): self
    {
        $this->supplierStreet = $supplierStreet;
        return $this;
    }

    public function getSupplierCity(): mixed
    {
        return $this->supplierCity;
    }

    public function setSupplierCity(mixed $supplierCity): self
    {
        $this->supplierCity = $supplierCity;
        return $this;
    }

    public function getSupplierZip(): mixed
    {
        return $this->supplierZip;
    }

    public function setSupplierZip(mixed $supplierZip): self
    {
        $this->supplierZip = $supplierZip;
        return $this;
    }

    public function getSupplierCountryCode(): mixed
    {
        return $this->supplierCountryCode;
    }

    public function setSupplierCountryCode(mixed $supplierCountryCode): self
    {
        $this->supplierCountryCode = $supplierCountryCode;
        return $this;
    }

    public function getSupplierCompanyId(): mixed
    {
        return $this->supplierCompanyId;
    }

    public function setSupplierCompanyId(mixed $supplierCompanyId): self
    {
        $this->supplierCompanyId = $supplierCompanyId;
        return $this;
    }

    public function getSupplierVat(): mixed
    {
        return $this->supplierVat;
    }

    public function setSupplierVat(mixed $supplierVat): self
    {
        $this->supplierVat = $supplierVat;
        return $this;
    }

    public function getSupplierName(): mixed
    {
        return $this->supplierName;
    }

    public function setSupplierName(mixed $supplierName): self
    {
        $this->supplierName = $supplierName;
        return $this;
    }

    public function getSupplierPhone(): mixed
    {
        return $this->supplierPhone;
    }

    public function setSupplierPhone(mixed $supplierPhone): self
    {
        $this->supplierPhone = $supplierPhone;
        return $this;
    }

    public function getBuyerMail(): mixed
    {
        return $this->buyerMail;
    }

    public function setBuyerMail(mixed $buyerMail): self
    {
        $this->buyerMail = $buyerMail;
        return $this;
    }

    public function getBuyerNumber(): mixed
    {
        return $this->buyerNumber;
    }

    public function setBuyerNumber(mixed $buyerNumber): self
    {
        $this->buyerNumber = $buyerNumber;
        return $this;
    }

    public function getBuyerStreet(): mixed
    {
        return $this->buyerStreet;
    }

    public function setBuyerStreet(mixed $buyerStreet): self
    {
        $this->buyerStreet = $buyerStreet;
        return $this;
    }

    public function getBuyerAdditionalStreet(): mixed
    {
        return $this->buyerAdditionalStreet;
    }

    public function setBuyerAdditionalStreet(mixed $buyerAdditionalStreet): self
    {
        $this->buyerAdditionalStreet = $buyerAdditionalStreet;
        return $this;
    }

    public function getBuyerCity(): mixed
    {
        return $this->buyerCity;
    }

    public function setBuyerCity(mixed $buyerCity): self
    {
        $this->buyerCity = $buyerCity;
        return $this;
    }

    public function getBuyerZip(): mixed
    {
        return $this->buyerZip;
    }

    public function setBuyerZip(mixed $buyerZip): self
    {
        $this->buyerZip = $buyerZip;
        return $this;
    }

    public function getBuyerCountryCode(): mixed
    {
        return $this->buyerCountryCode;
    }

    public function setBuyerCountryCode(mixed $buyerCountryCode): self
    {
        $this->buyerCountryCode = $buyerCountryCode;
        return $this;
    }

    public function getBuyerCompanyName(): mixed
    {
        return $this->buyerCompanyName;
    }

    public function setBuyerCompanyName(mixed $buyerCompanyName): self
    {
        $this->buyerCompanyName = $buyerCompanyName;
        return $this;
    }

    public function getBuyerPhone(): mixed
    {
        return $this->buyerPhone;
    }

    public function setBuyerPhone(mixed $buyerPhone): self
    {
        $this->buyerPhone = $buyerPhone;
        return $this;
    }

    public function getBuyerEmail(): mixed
    {
        return $this->buyerEmail;
    }

    public function setBuyerEmail(mixed $buyerEmail): self
    {
        $this->buyerEmail = $buyerEmail;
        return $this;
    }

    /** @return list<XRechnungInvoiceLineItem> */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    public function addLineItem(XRechnungInvoiceLineItem $lineItem): void
    {
        $this->lineItems[] = $lineItem;
    }

    public function getInvoiceNumber(): mixed
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(mixed $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getInvoiceDate(): mixed
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(mixed $invoiceDate): self
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function getCustomerNumber(): mixed
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(mixed $customerNumber): self
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getNetAmount(): mixed
    {
        return $this->netAmount;
    }

    public function setNetAmount(mixed $netAmount): self
    {
        $this->netAmount = $netAmount;
        return $this;
    }

    public function getTaxAmount(): mixed
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(mixed $taxAmount): self
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getGrossAmount(): mixed
    {
        return $this->grossAmount;
    }

    public function setGrossAmount(mixed $grossAmount): self
    {
        $this->grossAmount = $grossAmount;
        return $this;
    }

    public function getBuyerName(): mixed
    {
        return $this->buyerName;
    }

    public function setBuyerName(mixed $buyerName): self
    {
        $this->buyerName = $buyerName;
        return $this;
    }

    public function getInvoiceType(): mixed
    {
        return $this->invoiceType;
    }

    public function setInvoiceType(mixed $invoiceType): self
    {
        $this->invoiceType = $invoiceType;
        return $this;
    }

    public function getRelatedInvoiceNumber(): mixed
    {
        return $this->relatedInvoiceNumber;
    }

    public function setRelatedInvoiceNumber(mixed $relatedInvoiceNumber): self
    {
        $this->relatedInvoiceNumber = $relatedInvoiceNumber;
        return $this;
    }
}
