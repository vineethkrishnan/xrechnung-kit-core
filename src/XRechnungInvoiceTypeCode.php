<?php

declare(strict_types=1);

namespace XrechnungKit;

/**
 * UNTDID 1001 invoice type codes used by XRechnung. Backed-int enum so cases
 * substitute directly into the placeholder pipeline as their numeric value.
 *
 * The set covers the subset XRechnung 3.0 documents commonly emit. A3 will
 * relocate this to XrechnungKit\Mapping\Enum and tighten the case set to the
 * five document types the kit officially supports at v1.0; for now the lift
 * preserves the L3 code set verbatim (with the REQUEST_FRO_PAYMENT typo fixed
 * to REQUEST_FOR_PAYMENT - no caller in the kit uses the misspelled symbol).
 */
enum XRechnungInvoiceTypeCode: int
{
    case REQUEST_FOR_PAYMENT = 71;
    case SERVICE_DEBIT_NOTE = 80;
    case METERED_SERVICES_INVOICE = 82;
    case DEBIT_NOTE_RELATED_TO_FINANCIAL_ADJUSTMENTS = 84;
    case TAX_NOTIFICATION = 102;
    case FINAL_PAYMENT_REQUEST_BASED_ON_COMPLETION_OF_WORK = 218;
    case PAYMENT_REQUEST_FOR_COMPLETED_UNITS = 219;
    case CREDIT_NOTE = 326;
    case COMMERCIAL_INVOICE_WHICH_INCLUDES_A_PACKING_LIST = 331;
    case COMMERCIAL_INVOICE = 380;
    case DEBIT_NOTE = 381;
    case COMMISSION_NOTE = 382;
    case PAYMENT_REMINDER = 383;
    case PREPAYMENT_INVOICE = 386;
    case TAX_INVOICE = 388;
    case FACTORED_INVOICE = 393;
    case CONSIGNMENT_INVOICE = 395;
    case FORWARDERS_INVOICE_DISCREPANCY_REPORT = 553;
    case INSURERS_INVOICE = 575;
    case FORWARDERS_INVOICE = 623;
    case FREIGHT_INVOICE = 780;
    case CLAIM_NOTIFICATION = 817;
    case CONSULAR_INVOICE = 870;
    case PARTIAL_CONSTRUCTION_INVOICE = 875;
    case PARTIAL_FINAL_CONSTRUCTION_INVOICE = 876;
    case FINAL_CONSTRUCTION_INVOICE = 877;
}
