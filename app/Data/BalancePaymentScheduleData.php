<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class BalancePaymentScheduleData extends Data
{
    public function __construct(
        public Price $monthly_amortization,
        public int $term_in_months,
        public float $interest_rate,
    ) {}
}
