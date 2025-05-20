<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Factories\{CalculatorFactory, ExtractorFactory};
use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType};
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use Brick\Math\RoundingMode;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::INCOME_REQUIREMENT)]
final class IncomeRequirementCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        $monthly_amortization = CalculatorFactory::make(CalculatorType::AMORTIZATION, $this->inputs)->total();
        $income_requirement_multiplier = ExtractorFactory::make(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER, $this->inputs)->value();

        return $monthly_amortization->dividedBy($income_requirement_multiplier, RoundingMode::HALF_UP);
    }

    public function toFloat(): float
    {
        return $this->calculate()->inclusive()->getAmount()->toFloat();
    }
}
