<?php

namespace LBHurtado\Mortgage\Data\Match;

use Spatie\LaravelData\Data;

class LoanProductData extends Data
{
    public function __construct(
        public string $code,
        public string $name,
        public float $tcp,
        public float $interest_rate,   // e.g. 0.0625
        public int $max_term_years,    // e.g. 30
        public float $max_loanable_percent, // e.g. 0.9 for 90% of TCP
        public float $disposable_income_multiplier = 0.35,
        public ?float $min_gmi = null, // optional: minimum gross monthly income
    ) {}
}
