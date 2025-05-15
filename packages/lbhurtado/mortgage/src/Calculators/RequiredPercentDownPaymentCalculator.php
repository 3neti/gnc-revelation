<?php

namespace LBHurtado\Mortgage\Calculators;

use Brick\Math\RoundingMode;
use LBHurtado\Mortgage\Factories\{CalculatorFactory, ExtractorFactory};
use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType};
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\ValueObjects\Percent;

#[CalculatorFor(CalculatorType::REQUIRED_PERCENT_DOWN_PAYMENT)]
class RequiredPercentDownPaymentCalculator extends BaseCalculator
{
    public function calculate(): Percent
    {
        // Clone original inputs
        $inputs = clone $this->inputs;
        // Override the down payment to 0%
        $inputs->order()->setPercentDownPayment(0.0);

        $required_equity = CalculatorFactory::make(CalculatorType::EQUITY, $inputs)->calculate()->toPrice()
            ->inclusive()
            ->getAmount()
            ->toFloat();
        $tcp = ExtractorFactory::make(ExtractorType::TOTAL_CONTRACT_PRICE, $inputs)->extract()
            ->inclusive()
            ->getAmount()
            ->toFloat();

        // Suggest percent as next whole percent (e.g., 30.01% → 31%)
        $percent = ceil(($required_equity / $tcp) * 100);

        return Percent::ofPercent($percent); // ← this handles 31% → 0.31 automatically
    }
}
