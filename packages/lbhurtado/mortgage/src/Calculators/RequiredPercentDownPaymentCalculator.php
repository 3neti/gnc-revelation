<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Enums\CalculatorType;
use LBHurtado\Mortgage\ValueObjects\Percent;

#[CalculatorFor(CalculatorType::REQUIRED_PERCENT_DOWN_PAYMENT)]
class RequiredPercentDownPaymentCalculator extends BaseCalculator
{
    public function calculate(): Percent
    {
        $loan_difference = CalculatorFactory::make(CalculatorType::EQUITY, $this->inputs)->calculate();
        $tcp = $this->inputs->property()->getTotalContractPrice()->inclusive();
        $percent = round($loan_difference->toPrice()->inclusive()->getAmount()->toFloat() / $tcp->getAmount()->toFloat(), 4);

        return Percent::ofFraction($percent);
    }
}
