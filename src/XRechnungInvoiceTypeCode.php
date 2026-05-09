<?php

namespace XrechnungKit;

/**
 * Class XRechnungInvoiceTypeCode
 *
 * This class represents different types of invoice codes used in XRechnung. Each constant within
 * the class corresponds to a specific type of invoice, identified by a unique numerical code.
 */
class XRechnungInvoiceTypeCode
{

    public const REQUEST_FRO_PAYMENT = 71;
    public const SERVICE_DEBIT_NOTE = 80;
    public const METERED_SERVICES_INVOICE = 82;
    public const DEBIT_NOTE_RELATED_TO_FINANCIAL_ADJUSTMENTS = 84;
    public const TAX_NOTIFICATION = 102;
    public const FINAL_PAYMENT_REQUEST_BASED_ON_COMPLETION_OF_WORK = 218;
    public const PAYMENT_REQUEST_FOR_COMPLETED_UNITS = 219;
    public const CREDIT_NOTE = 326;
    public const COMMERCIAL_INVOICE_WHICH_INCLUDES_A_PACKING_LIST = 331;
    public const COMMERCIAL_INVOICE = 380;
    public const DEBIT_NOTE = 381;
    public const COMMISSION_NOTE = 382;
    public const PAYMENT_REMINDER = 383;
    public const PREPAYMENT_INVOICE = 386;
    public const TAX_INVOICE = 388;
    public const FACTORED_INVOICE = 393;
    public const CONSIGNMENT_INVOICE = 395;
    public const FORWARDERS_INVOICE_DISCREPANCY_REPORT = 553;
    public const INSURERS_INVOICE = 575;
    public const FORWARDERS_INVOICE = 623;
    public const FREIGHT_INVOICE = 780;
    public const CLAIM_NOTIFICATION = 817;
    public const CONSULAR_INVOICE = 870;
    public const PARTIAL_CONSTRUCTION_INVOICE = 875;
    public const PARTIAL_FINAL_CONSTRUCTION_INVOICE = 876;
    public const FINAL_CONSTRUCTION_INVOICE = 877;

}
