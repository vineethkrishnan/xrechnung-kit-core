<?php

declare(strict_types=1);

namespace XrechnungKit\Mapping;

/**
 * UNTDID 4461 payment means codes the kit emits at v1.0. Subset chosen by
 * the architecture (section 5.1) to cover the cases real consumers ship: SEPA
 * (CT and DD), card payments (credit and bank), cash, and non-SEPA bank
 * transfers.
 */
enum PaymentMeansCode: int
{
    case CASH = 10;
    case CREDIT_TRANSFER = 30;
    case BANK_TRANSFER = 42;
    case BANK_CARD = 48;
    case CREDIT_CARD = 54;
    case SEPA_CREDIT_TRANSFER = 58;
    case SEPA_DIRECT_DEBIT = 59;
}
