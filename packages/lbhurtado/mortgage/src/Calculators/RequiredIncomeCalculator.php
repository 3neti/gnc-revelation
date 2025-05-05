<?php

namespace LBHurtado\Mortgage\Calculators;


use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Enums\CalculatorType;
use Brick\Math\RoundingMode;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::REQUIRED_INCOME)]
class RequiredIncomeCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        $amortization = CalculatorFactory::make(CalculatorType::AMORTIZATION, $this->inputs)->total();
        $income_requirement_multiplier = $this->inputs->buyer()->getLendingInstitution()->getIncomeRequirementMultiplier()->value();

        return $amortization->dividedBy($income_requirement_multiplier, RoundingMode::HALF_UP);
    }
}
