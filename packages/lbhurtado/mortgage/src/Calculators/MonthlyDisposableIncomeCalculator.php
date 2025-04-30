<?php

namespace LBHurtado\Mortgage\Calculators;

use Brick\Money\Money;
use LBHurtado\Mortgage\Exceptions\IncomeRequirementMultiplierNotSetException;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Enums\CalculatorType;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::DISPOSABLE_INCOME)]
final class MonthlyDisposableIncomeCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        return new Price($this->inputs->buyer()->getJointMonthlyDisposableIncome()->inclusive()); //try updating the percent GMI from parent buyer to co-borrower

//        $multiplier = $this->inputs->income->income_requirement_multiplier?->value();
//        if ($multiplier === null) {
//            throw new IncomeRequirementMultiplierNotSetException();
//        }
//        $gross = clone($this->inputs->income->gross_monthly_income);
//
//        return $gross->multipliedBy($multiplier);
    }
}
