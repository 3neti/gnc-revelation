<?php

namespace LBHurtado\Mortgage\Data;

use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class MonthlyAmortizationBreakdownData extends Data
{
    public function __construct(
        public Price $principal,
        public Price $add_ons,
    ) {}
}
