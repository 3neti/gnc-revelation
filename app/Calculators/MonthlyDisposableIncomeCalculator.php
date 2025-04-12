<?php

namespace App\Calculators;

use App\Exceptions\IncomeRequirementMultiplierNotSetException;
use App\Attributes\CalculatorFor;
use Whitecube\Price\Price;
use App\Enums\CalculatorType;

#[CalculatorFor(CalculatorType::DISPOSABLE_INCOME)]
final class MonthlyDisposableIncomeCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        $multiplier = $this->inputs->income->income_requirement_multiplier?->value();

        if ($multiplier === null) {
            throw new IncomeRequirementMultiplierNotSetException();
        }

        $gross = clone($this->inputs->income->gross_monthly_income);


        return $gross->multipliedBy($multiplier);
    }
}
