<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class MatchResultData extends Data
{
    public function __construct(
        public bool $qualified,
        public string $product_code,
        public Price $monthly_amortization,
        public Price $income_required,
        public Price $suggested_equity,
        public float $gap,
        public string $reason
    ) {}
}
