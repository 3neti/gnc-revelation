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
        $multiplier = ExtractorFactory::make(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER, $this->inputs)->extract()->value();
        if ($multiplier === null) {
            throw new IncomeRequirementMultiplierNotSetException();
        }
        $gross = $this->inputs->buyer()->getJointMonthlyGrossIncome();

        return $gross->multipliedBy($multiplier);
    }

    public function toFloat(): float
    {
        return $this->calculate()->inclusive()->getAmount()->toFloat();
    }
}
