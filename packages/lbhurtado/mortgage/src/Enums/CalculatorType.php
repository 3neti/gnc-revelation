<?php

namespace LBHurtado\Mortgage\Enums;

enum CalculatorType: string
{
    case AMORTIZATION = 'amortization';
    case DISPOSABLE_INCOME = 'disposable_income';
    case PRESENT_VALUE = 'present_value';
    case EQUITY = 'equity';
    case CASH_OUT = 'cash_out';
    case LOANABLE_AMOUNT = 'loanable_amount';
    case FEES = 'fees';
    case REQUIRED_INCOME = 'required_income';
    case INCOME_GAP = 'income_gap';
    case LOAN_QUALIFICATION = 'loan_qualification';
    case REQUIRED_PERCENT_DOWN_PAYMENT = 'required_percent_down_payment';
}
