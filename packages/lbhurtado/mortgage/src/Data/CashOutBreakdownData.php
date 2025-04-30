<?php

namespace LBHurtado\Mortgage\Data;

use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class CashOutBreakdownData extends Data
{
    public function __construct(
        public Price $down_payment,
        public Price $miscellaneous_fee,
        public Price $processing_fee,
        public Price $total, // Injected by calculator
    ) {}
}
