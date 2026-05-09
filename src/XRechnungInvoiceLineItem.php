<?php

namespace XrechnungKit;

class XRechnungInvoiceLineItem
{
    private mixed $itemNumber = null;
    private mixed $itemQuantity = null;
    private mixed $itemPrice = null;
    private mixed $itemUnitPrice = null;
    private mixed $itemStartDate = null;
    private mixed $itemEndDate = null;
    private mixed $itemDescription = null;
    private mixed $itemResource = null;
    private mixed $itemTaxCategory = null;
    private mixed $itemTax = null;
    private mixed $itemTaxScheme = null;
    private mixed $itemAllowanceCharge = null;

    public function getItemNumber(): mixed
    {
        return $this->itemNumber;
    }

    public function setItemNumber(mixed $itemNumber): XRechnungInvoiceLineItem
    {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function getItemQuantity(): mixed
    {
        return $this->itemQuantity;
    }

    public function setItemQuantity(mixed $itemQuantity): XRechnungInvoiceLineItem
    {
        $this->itemQuantity = $itemQuantity;
        return $this;
    }

    public function getItemPrice(): mixed
    {
        return $this->itemPrice;
    }

    public function setItemPrice(mixed $itemPrice): XRechnungInvoiceLineItem
    {
        $this->itemPrice = $itemPrice;
        return $this;
    }

    public function getItemStartDate(): mixed
    {
        return $this->itemStartDate;
    }

    public function setItemStartDate(mixed $itemStartDate): XRechnungInvoiceLineItem
    {
        $this->itemStartDate = $itemStartDate;
        return $this;
    }

    public function getItemEndDate(): mixed
    {
        return $this->itemEndDate;
    }

    public function setItemEndDate(mixed $itemEndDate): XRechnungInvoiceLineItem
    {
        $this->itemEndDate = $itemEndDate;
        return $this;
    }

    public function getItemDescription(): mixed
    {
        return $this->itemDescription;
    }

    public function setItemDescription(mixed $itemDescription): XRechnungInvoiceLineItem
    {
        $this->itemDescription = $itemDescription;
        return $this;
    }

    public function getItemResource(): mixed
    {
        return $this->itemResource;
    }

    public function setItemResource(mixed $itemResource): XRechnungInvoiceLineItem
    {
        $this->itemResource = $itemResource;
        return $this;
    }

    public function getItemTaxCategory(): mixed
    {
        return $this->itemTaxCategory;
    }

    public function setItemTaxCategory(mixed $itemTaxCategory): XRechnungInvoiceLineItem
    {
        $this->itemTaxCategory = $itemTaxCategory;
        return $this;
    }

    public function getItemTax(): mixed
    {
        return $this->itemTax;
    }

    public function setItemTax(mixed $itemTax): XRechnungInvoiceLineItem
    {
        $this->itemTax = $itemTax;
        return $this;
    }

    public function getItemTaxScheme(): mixed
    {
        return $this->itemTaxScheme;
    }

    public function setItemTaxScheme(mixed $itemTaxScheme): XRechnungInvoiceLineItem
    {
        $this->itemTaxScheme = $itemTaxScheme;
        return $this;
    }

    public function getItemUnitPrice(): mixed
    {
        return $this->itemUnitPrice;
    }

    public function setItemUnitPrice(mixed $itemUnitPrice): XRechnungInvoiceLineItem
    {
        $this->itemUnitPrice = $itemUnitPrice;
        return $this;
    }

    public function getItemAllowanceCharge(): mixed
    {
        return $this->itemAllowanceCharge;
    }

    public function setItemAllowanceCharge(mixed $itemAllowanceCharge): XRechnungInvoiceLineItem
    {
        $this->itemAllowanceCharge = $itemAllowanceCharge;
        return $this;
    }
}
