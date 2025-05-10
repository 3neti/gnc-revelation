<?php

namespace LBHurtado\Mortgage\Calculators;

use Brick\Money\Money;
use LBHurtado\Mortgage\Enums\ExtractorType;
use LBHurtado\Mortgage\Exceptions\IncomeRequirementMultiplierNotSetException;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Enums\CalculatorType;
use LBHurtado\Mortgage\Factories\ExtractorFactory;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::DISPOSABLE_INCOME)]
final class MonthlyDisposableIncomeCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
//        return new Price($this->inputs->buyer()->getJointMonthlyDisposableIncome()->inclusive()); //try updating the percent GMI from parent buyer to co-borrower

        $multiplier = ExtractorFactory::make(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER, $this->inputs)->extract()->value();

        if ($multiplier === null) {
            throw new IncomeRequirementMultiplierNotSetException();
        }
//        $gross = clone($this->inputs->income->gross_monthly_income);

        $gross = $this->inputs->buyer()->getJointMonthlyGrossIncome();

        return $gross->multipliedBy($multiplier);
    }
}
