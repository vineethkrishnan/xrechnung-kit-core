<?php

namespace XrechnungKit;

class XRechnungEntity
{
    private $invoiceNumber;
    private $invoiceDate;
    private $typeCode;
    private $customerNumber;
    private $note;
    private $currencyCode;
    private $buyerReferenceNumber;
    private $paymentCode;
    private $financialNumber;
    private $paymentNote;
    private $taxCategory;
    private $tax;
    private $taxScheme;
    private $netAmount;
    private $taxAmount;
    private $grossAmount;
    private $downPayment;
    private $payableAmount;
    private $cautionDocuments;
    private $depositDocuments;
    private $supplierEmail;
    private $supplierCompanyName;
    private $supplierStreet;
    private $supplierCity;
    private $supplierZip;
    private $supplierCountryCode;
    private $supplierCompanyId;
    private $supplierVat;
    private $supplierName;
    private $supplierPhone;
    private $buyerMail;
    private $buyerNumber;
    private $buyerStreet;
    private $buyerAdditionalStreet;
    private $buyerCity;
    private $buyerZip;
    private $buyerCountryCode;
    private $buyerCompanyName;
    private $buyerName;
    private $buyerPhone;
    private $buyerEmail;

    private $lineItems = [];

    private $invoiceType;

    private $relatedInvoiceNumber;

    public function getTypeCode()
    {
        return $this->typeCode;
    }

    public function setTypeCode($typeCode): XRechnungEntity
    {
        $this->typeCode = $typeCode;
        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note): XRechnungEntity
    {
        $this->note = $note;
        return $this;
    }

    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode($currencyCode): XRechnungEntity
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    public function getBuyerReferenceNumber()
    {
        return $this->buyerReferenceNumber;
    }

    public function setBuyerReferenceNumber($buyerReferenceNumber): XRechnungEntity
    {
        $this->buyerReferenceNumber = $buyerReferenceNumber;
        return $this;
    }

    public function getPaymentCode()
    {
        return $this->paymentCode;
    }

    public function setPaymentCode($paymentCode): XRechnungEntity
    {
        $this->paymentCode = $paymentCode;
        return $this;
    }

    public function getFinancialNumber()
    {
        return $this->financialNumber;
    }

    public function setFinancialNumber($financialNumber): XRechnungEntity
    {
        $this->financialNumber = $financialNumber;
        return $this;
    }

    public function getPaymentNote()
    {
        return $this->paymentNote;
    }

    public function setPaymentNote($paymentNote): XRechnungEntity
    {
        $this->paymentNote = $paymentNote;
        return $this;
    }

    public function getTaxCategory()
    {
        return $this->taxCategory;
    }

    public function setTaxCategory($taxCategory): XRechnungEntity
    {
        $this->taxCategory = $taxCategory;
        return $this;
    }

    public function getTax()
    {
        return $this->tax;
    }

    public function setTax($tax): XRechnungEntity
    {
        $this->tax = $tax;
        return $this;
    }

    public function getTaxScheme()
    {
        return $this->taxScheme;
    }

    public function setTaxScheme($taxScheme): XRechnungEntity
    {
        $this->taxScheme = $taxScheme;
        return $this;
    }

    public function getDownPayment()
    {
        return $this->downPayment;
    }

    public function setDownPayment($downPayment): XRechnungEntity
    {
        $this->downPayment = $downPayment;
        return $this;
    }

    public function getPayableAmount()
    {
        return $this->payableAmount;
    }

    public function setPayableAmount($payableAmount): XRechnungEntity
    {
        $this->payableAmount = $payableAmount;
        return $this;
    }

    public function getCautionDocuments(): array
    {
        return $this->cautionDocuments;
    }

    public function setCautionDocuments(array $cautionDocuments = []): XRechnungEntity
    {
        $this->cautionDocuments = $cautionDocuments;
        return $this;
    }

    public function getDepositDocuments(): array
    {
        return $this->depositDocuments;
    }

    public function setDepositDocuments(array $depositDocuments = []): XRechnungEntity
    {
        $this->depositDocuments = $depositDocuments;
        return $this;
    }

    public function getSupplierEmail()
    {
        return $this->supplierEmail;
    }

    public function setSupplierEmail($supplierEmail): XRechnungEntity
    {
        $this->supplierEmail = $supplierEmail;
        return $this;
    }

    public function getSupplierCompanyName()
    {
        return $this->supplierCompanyName;
    }

    public function setSupplierCompanyName($supplierCompanyName): XRechnungEntity
    {
        $this->supplierCompanyName = $supplierCompanyName;
        return $this;
    }

    public function getSupplierStreet()
    {
        return $this->supplierStreet;
    }

    public function setSupplierStreet($supplierStreet): XRechnungEntity
    {
        $this->supplierStreet = $supplierStreet;
        return $this;
    }

    public function getSupplierCity()
    {
        return $this->supplierCity;
    }

    public function setSupplierCity($supplierCity): XRechnungEntity
    {
        $this->supplierCity = $supplierCity;
        return $this;
    }

    public function getSupplierZip()
    {
        return $this->supplierZip;
    }

    public function setSupplierZip($supplierZip): XRechnungEntity
    {
        $this->supplierZip = $supplierZip;
        return $this;
    }

    public function getSupplierCountryCode()
    {
        return $this->supplierCountryCode;
    }

    public function setSupplierCountryCode($supplierCountryCode): XRechnungEntity
    {
        $this->supplierCountryCode = $supplierCountryCode;
        return $this;
    }

    public function getSupplierCompanyId()
    {
        return $this->supplierCompanyId;
    }

    public function setSupplierCompanyId($supplierCompanyId): XRechnungEntity
    {
        $this->supplierCompanyId = $supplierCompanyId;
        return $this;
    }

    public function getSupplierVat()
    {
        return $this->supplierVat;
    }

    public function setSupplierVat($supplierVat): XRechnungEntity
    {
        $this->supplierVat = $supplierVat;
        return $this;
    }

    public function getSupplierName()
    {
        return $this->supplierName;
    }

    public function setSupplierName($supplierName): XRechnungEntity
    {
        $this->supplierName = $supplierName;
        return $this;
    }

    public function getSupplierPhone()
    {
        return $this->supplierPhone;
    }

    public function setSupplierPhone($supplierPhone): XRechnungEntity
    {
        $this->supplierPhone = $supplierPhone;
        return $this;
    }

    public function getBuyerMail()
    {
        return $this->buyerMail;
    }

    public function setBuyerMail($buyerMail): XRechnungEntity
    {
        $this->buyerMail = $buyerMail;
        return $this;
    }

    public function getBuyerNumber()
    {
        return $this->buyerNumber;
    }

    public function setBuyerNumber($buyerNumber): XRechnungEntity
    {
        $this->buyerNumber = $buyerNumber;
        return $this;
    }

    public function getBuyerStreet()
    {
        return $this->buyerStreet;
    }

    public function setBuyerStreet($buyerStreet): XRechnungEntity
    {
        $this->buyerStreet = $buyerStreet;
        return $this;
    }

    public function getBuyerAdditionalStreet()
    {
        return $this->buyerAdditionalStreet;
    }

    public function setBuyerAdditionalStreet($buyerAdditionalStreet): XRechnungEntity
    {
        $this->buyerAdditionalStreet = $buyerAdditionalStreet;
        return $this;
    }

    public function getBuyerCity()
    {
        return $this->buyerCity;
    }

    public function setBuyerCity($buyerCity): XRechnungEntity
    {
        $this->buyerCity = $buyerCity;
        return $this;
    }

    public function getBuyerZip()
    {
        return $this->buyerZip;
    }

    public function setBuyerZip($buyerZip): XRechnungEntity
    {
        $this->buyerZip = $buyerZip;
        return $this;
    }

    public function getBuyerCountryCode()
    {
        return $this->buyerCountryCode;
    }

    public function setBuyerCountryCode($buyerCountryCode): XRechnungEntity
    {
        $this->buyerCountryCode = $buyerCountryCode;
        return $this;
    }

    public function getBuyerCompanyName()
    {
        return $this->buyerCompanyName;
    }

    public function setBuyerCompanyName($buyerCompanyName): XRechnungEntity
    {
        $this->buyerCompanyName = $buyerCompanyName;
        return $this;
    }

    public function getBuyerPhone()
    {
        return $this->buyerPhone;
    }

    public function setBuyerPhone($buyerPhone): XRechnungEntity
    {
        $this->buyerPhone = $buyerPhone;
        return $this;
    }

    public function getBuyerEmail()
    {
        return $this->buyerEmail;
    }

    public function setBuyerEmail($buyerEmail): XRechnungEntity
    {
        $this->buyerEmail = $buyerEmail;
        return $this;
    }

    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    public function addLineItem(XRechnungInvoiceLineItem $lineItem)
    {
        $this->lineItems[] = $lineItem;
    }

    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber($invoiceNumber): XRechnungEntity
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate($invoiceDate): XRechnungEntity
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber($customerNumber): XRechnungEntity
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getNetAmount()
    {
        return $this->netAmount;
    }

    public function setNetAmount($netAmount): XRechnungEntity
    {
        $this->netAmount = $netAmount;
        return $this;
    }

    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    public function setTaxAmount($taxAmount): XRechnungEntity
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getGrossAmount()
    {
        return $this->grossAmount;
    }

    public function setGrossAmount($grossAmount): XRechnungEntity
    {
        $this->grossAmount = $grossAmount;
        return $this;
    }

    public function getBuyerName()
    {
        return $this->buyerName;
    }

    public function setBuyerName($buyerName): XRechnungEntity
    {
        $this->buyerName = $buyerName;
        return $this;
    }

    public function getInvoiceType()
    {
        return $this->invoiceType;
    }

    public function setInvoiceType($invoiceType): XRechnungEntity
    {
        $this->invoiceType = $invoiceType;
        return $this;
    }

    public function getRelatedInvoiceNumber()
    {
        return $this->relatedInvoiceNumber;
    }

    public function setRelatedInvoiceNumber($relatedInvoiceNumber): XRechnungEntity
    {
        $this->relatedInvoiceNumber = $relatedInvoiceNumber;
        return $this;
    }

}
