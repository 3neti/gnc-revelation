<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class CashOutScheduleData extends Data
{
    public function __construct(
        public Price $total_cash_out,
        public Price $downpayment,
        public int $dp_term_months,
        public Price $monthly_dp_payment,
    ) {}
}
