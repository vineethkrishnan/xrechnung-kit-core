<?php

declare(strict_types=1);

namespace XrechnungKit;

/**
 * EN 16931 BG-23 tax category codes. Backed-string enum so cases substitute
 * directly into the placeholder pipeline as their letter code.
 *
 * A3 will relocate this to XrechnungKit\Mapping\Enum.
 */
enum XRechnungTaxCategory: string
{
    case VAT_REVERSE_CHARGE = 'AE';
    case STANDARD_RATE = 'S';
    case EXEMPT_FROM_TAX = 'E';
    case SERVICES_OUTSIDE_SCOPE_OF_TAX = 'O';
    case ZERO_RATED_GOODS = 'Z';
    case VAT_EXEMPT_FOR_EEA_INTRA_COMMUNITY_SUPPLY_OF_GOODS_AND_SERVICES = 'K';
    case FREE_EXPORT_ITEM_VAT_NOT_CHARGED = 'G';
    case TAX_FOR_PRODUCTION_SERVICES_AND_IMPORTATION_IN_CEUTA_AND_MELILLA = 'M';
    case CANARY_ISLANDS_GENERAL_INDIRECT_TAX = 'L';
    case TRANSFERRED_VAT_IN_ITALY = 'B';
}
