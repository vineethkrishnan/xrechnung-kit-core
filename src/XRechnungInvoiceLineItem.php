<?php

declare(strict_types=1);

namespace XrechnungKit;

final class XRechnungInvoiceLineItem
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

    public function setItemNumber(mixed $itemNumber): self
    {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function getItemQuantity(): mixed
    {
        return $this->itemQuantity;
    }

    public function setItemQuantity(mixed $itemQuantity): self
    {
        $this->itemQuantity = $itemQuantity;
        return $this;
    }

    public function getItemPrice(): mixed
    {
        return $this->itemPrice;
    }

    public function setItemPrice(mixed $itemPrice): self
    {
        $this->itemPrice = $itemPrice;
        return $this;
    }

    public function getItemStartDate(): mixed
    {
        return $this->itemStartDate;
    }

    public function setItemStartDate(mixed $itemStartDate): self
    {
        $this->itemStartDate = $itemStartDate;
        return $this;
    }

    public function getItemEndDate(): mixed
    {
        return $this->itemEndDate;
    }

    public function setItemEndDate(mixed $itemEndDate): self
    {
        $this->itemEndDate = $itemEndDate;
        return $this;
    }

    public function getItemDescription(): mixed
    {
        return $this->itemDescription;
    }

    public function setItemDescription(mixed $itemDescription): self
    {
        $this->itemDescription = $itemDescription;
        return $this;
    }

    public function getItemResource(): mixed
    {
        return $this->itemResource;
    }

    public function setItemResource(mixed $itemResource): self
    {
        $this->itemResource = $itemResource;
        return $this;
    }

    public function getItemTaxCategory(): mixed
    {
        return $this->itemTaxCategory;
    }

    public function setItemTaxCategory(mixed $itemTaxCategory): self
    {
        $this->itemTaxCategory = $itemTaxCategory;
        return $this;
    }

    public function getItemTax(): mixed
    {
        return $this->itemTax;
    }

    public function setItemTax(mixed $itemTax): self
    {
        $this->itemTax = $itemTax;
        return $this;
    }

    public function getItemTaxScheme(): mixed
    {
        return $this->itemTaxScheme;
    }

    public function setItemTaxScheme(mixed $itemTaxScheme): self
    {
        $this->itemTaxScheme = $itemTaxScheme;
        return $this;
    }

    public function getItemUnitPrice(): mixed
    {
        return $this->itemUnitPrice;
    }

    public function setItemUnitPrice(mixed $itemUnitPrice): self
    {
        $this->itemUnitPrice = $itemUnitPrice;
        return $this;
    }

    public function getItemAllowanceCharge(): mixed
    {
        return $this->itemAllowanceCharge;
    }

    public function setItemAllowanceCharge(mixed $itemAllowanceCharge): self
    {
        $this->itemAllowanceCharge = $itemAllowanceCharge;
        return $this;
    }
}
