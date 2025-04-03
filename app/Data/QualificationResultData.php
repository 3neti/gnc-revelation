<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Whitecube\Price\Price;
use Brick\Money\Money;

class QualificationResultData extends Data
{
    public function __construct(
        public bool $qualifies,
        public float $gap,
        public Price $suggested_equity,
        public string $reason,
        public Price $monthly_amortization,
        public Money $income_required,
        public Money $disposable_income,
    ) {}
}
