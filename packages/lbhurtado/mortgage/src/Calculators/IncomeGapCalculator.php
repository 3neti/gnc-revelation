<?php

namespace LBHurtado\Mortgage\Calculators;


use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\Enums\CalculatorType;
use Brick\Math\RoundingMode;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::INCOME_GAP)]
class IncomeGapCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        $monthly_amortization = CalculatorFactory::make(CalculatorType::AMORTIZATION, $this->inputs)->total()->inclusive();
        $disposable_income = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $this->inputs)->calculate();
        $gap = max(0, round($monthly_amortization->getAmount()->toFloat() - $disposable_income->getAmount()->toFloat(), 2));

        return MoneyFactory::price($gap);
    }

    public function toFloat(): float
    {
        return $this->calculate()->inclusive()->getAmount()->toFloat();
    }
}
