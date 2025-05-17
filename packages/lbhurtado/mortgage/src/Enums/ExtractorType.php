<?php

namespace LBHurtado\Mortgage\Enums;

enum ExtractorType: string
{
    case INCOME_REQUIREMENT_MULTIPLIER = 'income_requirement_multiplier';
    case LENDING_INSTITUTION = 'lending_institution';
    case INTEREST_RATE = 'interest_rate';
    case TOTAL_CONTRACT_PRICE = 'total_contract_price';
    case PERCENT_DOWN_PAYMENT = 'percent_down_payment';
    case PERCENT_MISCELLANEOUS_FEES = 'percent_miscellaneous_fees';
    case PROCESSING_FEE = 'processing_fee';
}
