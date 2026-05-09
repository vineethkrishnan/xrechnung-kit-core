<?php

namespace XrechnungKit;

/**
 * XRechnungTemplate class provides functionalities to handle XRechnung template files.
 * If a custom template file exists, its content is retrieved. Otherwise, a default
 * template content is provided.
 */
class XRechnungTemplate
{
    private static function templatesDir(): string
    {
        return dirname(__DIR__) . '/resources/templates';
    }

    /**
     * Returns the content of the XRechnung template file.
     *
     * @param string|null $type The type of the template file. Default is 'invoice'.
     * @return string The content of the template file as a string.
     */
    public static function getTemplate(?string $type = 'invoice'): string
    {
        if ($type === null) {
            $type = 'invoice';
        }

        if ($type == 'deposit') {
            $type = 'caution'; // Deposit uses the same caution template
        }
        $templatePath = self::templatesDir() . '/XRechnung' . ucfirst($type) . 'Template.xml';

        if (!file_exists($templatePath)) {
            return static::defaultContent();
        }
        $contents = file_get_contents($templatePath);
        return $contents !== false ? $contents : static::defaultContent();
    }

    public static function getCautionDepositEntityTemplate(): string
    {
        $templatePath = self::templatesDir() . '/partials/CautionDepositEntityTemplate.xml';
        if (!file_exists($templatePath)) {
            return '';
        }
        $contents = file_get_contents($templatePath);
        return $contents !== false ? $contents : '';
    }

    public static function getSupplierPartyTemplate(): string
    {
        $templatePath = self::templatesDir() . '/partials/SupplierPartyTemplate.xml';
        if (!file_exists($templatePath)) {
            return '';
        }
        $contents = file_get_contents($templatePath);
        return $contents !== false ? $contents : '';
    }

    public static function getBuyerPartyTemplate(): string
    {
        $templatePath = self::templatesDir() . '/partials/BuyerPartyTemplate.xml';
        if (!file_exists($templatePath)) {
            return '';
        }
        $contents = file_get_contents($templatePath);
        return $contents !== false ? $contents : '';
    }

    public static function getInvoiceLineTemplate(): string
    {
        $templatePath = self::templatesDir() . '/partials/InvoiceLineTemplate.xml';
        if (!file_exists($templatePath)) {
            return '';
        }
        $contents = file_get_contents($templatePath);
        return $contents !== false ? $contents : '';
    }

    public static function getCautionDepositLineTemplate(): string
    {
        $templatePath = self::templatesDir() . '/partials/CautionDepositLineItemTemplate.xml';
        if (!file_exists($templatePath)) {
            return '';
        }
        $contents = file_get_contents($templatePath);
        return $contents !== false ? $contents : '';
    }

    public static function getCreditLineTemplate(): string
    {
        $templatePath = self::templatesDir() . '/partials/CancelCreditLineItemTemplate.xml';
        if (!file_exists($templatePath)) {
            return '';
        }
        $contents = file_get_contents($templatePath);
        return $contents !== false ? $contents : '';
    }

    public static function getAllowanceChargeTemplate(): string
    {
        $templatePath = self::templatesDir() . '/partials/InvoiceLineAllowanceChargeTemplate.xml';
        if (!file_exists($templatePath)) {
            return '';
        }
        $contents = file_get_contents($templatePath);
        return $contents !== false ? $contents : '';
    }

    /**
     * Generates the default XML content for a CrossIndustryInvoice document.
     *
     * @return string The default XML content as a string.
     */
    public static function defaultContent(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100"
                          xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"
                          xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">
    <rsm:ExchangedDocumentContext>
        <ram:TestIndicator>
            <udt:Indicator>false</udt:Indicator>
        </ram:TestIndicator>
    </rsm:ExchangedDocumentContext>
    <rsm:ExchangedDocument>
        <ram:ID>{INVOICE_NUMBER}</ram:ID>
        <ram:Name>Invoice</ram:Name>
        <ram:TypeCode>380</ram:TypeCode>
        <ram:IssueDateTime>
            <udt:DateTimeString format="102">{INVOICE_DATE}</udt:DateTimeString>
        </ram:IssueDateTime>
    </rsm:ExchangedDocument>
    <rsm:SupplyChainTradeTransaction>
        <ram:ApplicableHeaderTradeAgreement>
            <ram:BuyerReference>{CUSTOMER_NUMBER}</ram:BuyerReference>
        </ram:ApplicableHeaderTradeAgreement>
        <ram:ApplicableHeaderTradeDelivery>
            <ram:ShipToTradeParty>
                <ram:Name>{BUYER_NAME}</ram:Name>
            </ram:ShipToTradeParty>
        </ram:ApplicableHeaderTradeDelivery>
        <ram:ApplicableHeaderTradeSettlement>
            <ram:SpecifiedTradeSettlementHeaderMonetarySummation>
                <ram:LineTotalAmount>{NET_AMOUNT}</ram:LineTotalAmount>
                <ram:TaxTotalAmount>{TAX_AMOUNT}</ram:TaxTotalAmount>
                <ram:GrandTotalAmount>{GROSS_AMOUNT}</ram:GrandTotalAmount>
            </ram:SpecifiedTradeSettlementHeaderMonetarySummation>
        </ram:ApplicableHeaderTradeSettlement>
    </rsm:SupplyChainTradeTransaction>
</rsm:CrossIndustryInvoice>
XML;
    }
}
