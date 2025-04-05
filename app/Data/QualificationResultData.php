<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Whitecube\Price\Price;
use Brick\Money\Money;

class QualificationResultData extends Data
{
    public function __construct(
        public bool   $qualifies,
        public float  $gap,
        public Price  $suggested_equity,
        public string $reason,
        public Price  $monthly_amortization,
        public Money  $income_required,
        public Money  $disposable_income,
        public float  $suggested_down_payment_percent,
        public float  $actual_down_payment,
        public float  $required_loanable,
        public float  $affordable_loanable,
        public Price  $required_down_payment,
        public Price $required_cash_out,
        public Price $balance_miscellaneous_fee,
        public Price $monthly_miscellaneous_fee_share
    ) {}
}
