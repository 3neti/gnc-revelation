<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class MonthlyAmortizationBreakdownData extends Data
{
    public function __construct(
        public Price $principal,
        public Price $mf,
        public Price $add_ons,
        public Price $total,
    ) {}
}
