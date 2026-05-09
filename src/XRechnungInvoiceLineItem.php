<?php

namespace XrechnungKit;

class XRechnungInvoiceLineItem
{

    private $itemNumber;
    private $itemQuantity;
    private $itemPrice;
    private $itemUnitPrice;
    private $itemStartDate;
    private $itemEndDate;
    private $itemDescription;
    private $itemResource;
    private $itemTaxCategory;
    private $itemTax;
    private $itemTaxScheme;
    private $itemAllowanceCharge;

    public function getItemNumber()
    {
        return $this->itemNumber;
    }

    public function setItemNumber($itemNumber): XRechnungInvoiceLineItem
    {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function getItemQuantity()
    {
        return $this->itemQuantity;
    }

    public function setItemQuantity($itemQuantity): XRechnungInvoiceLineItem
    {
        $this->itemQuantity = $itemQuantity;
        return $this;
    }

    public function getItemPrice()
    {
        return $this->itemPrice;
    }

    public function setItemPrice($itemPrice): XRechnungInvoiceLineItem
    {
        $this->itemPrice = $itemPrice;
        return $this;
    }

    public function getItemStartDate()
    {
        return $this->itemStartDate;
    }

    public function setItemStartDate($itemStartDate): XRechnungInvoiceLineItem
    {
        $this->itemStartDate = $itemStartDate;
        return $this;
    }

    public function getItemEndDate()
    {
        return $this->itemEndDate;
    }

    public function setItemEndDate($itemEndDate): XRechnungInvoiceLineItem
    {
        $this->itemEndDate = $itemEndDate;
        return $this;
    }

    public function getItemDescription()
    {
        return $this->itemDescription;
    }

    public function setItemDescription($itemDescription): XRechnungInvoiceLineItem
    {
        $this->itemDescription = $itemDescription;
        return $this;
    }

    public function getItemResource()
    {
        return $this->itemResource;
    }

    public function setItemResource($itemResource): XRechnungInvoiceLineItem
    {
        $this->itemResource = $itemResource;
        return $this;
    }

    public function getItemTaxCategory()
    {
        return $this->itemTaxCategory;
    }

    public function setItemTaxCategory($itemTaxCategory): XRechnungInvoiceLineItem
    {
        $this->itemTaxCategory = $itemTaxCategory;
        return $this;
    }

    public function getItemTax()
    {
        return $this->itemTax;
    }

    public function setItemTax($itemTax): XRechnungInvoiceLineItem
    {
        $this->itemTax = $itemTax;
        return $this;
    }

    public function getItemTaxScheme()
    {
        return $this->itemTaxScheme;
    }

    public function setItemTaxScheme($itemTaxScheme): XRechnungInvoiceLineItem
    {
        $this->itemTaxScheme = $itemTaxScheme;
        return $this;
    }

    public function getItemUnitPrice()
    {
        return $this->itemUnitPrice;
    }

    public function setItemUnitPrice($itemUnitPrice): XRechnungInvoiceLineItem
    {
        $this->itemUnitPrice = $itemUnitPrice;
        return $this;
    }

    public function getItemAllowanceCharge()
    {
        return $this->itemAllowanceCharge;
    }

    public function setItemAllowanceCharge($itemAllowanceCharge): XRechnungInvoiceLineItem
    {
        $this->itemAllowanceCharge = $itemAllowanceCharge;
        return $this;
    }

}
