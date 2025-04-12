<?php

namespace App\Data\Inputs;

use App\Contracts\PropertyInterface;
use App\Contracts\OrderInterface;
use App\Contracts\BuyerInterface;
use App\ValueObjects\Percent;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class IncomeInputsData extends Data
{
    public function __construct(
        public Price  $gross_monthly_income,
        public ?Percent $income_requirement_multiplier = null
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            gross_monthly_income: $buyer->getMonthlyGrossIncome(),
            income_requirement_multiplier: $order->getIncomeRequirementMultiplier() ?? ($property->getIncomeRequirementMultiplier() ?? $buyer->getIncomeRequirementMultiplier()),
        );
    }
}
