<?php

namespace App\DataObjects;

use Whitecube\Price\Price;
use Brick\Money\Money;

readonly class QualificationResult
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
