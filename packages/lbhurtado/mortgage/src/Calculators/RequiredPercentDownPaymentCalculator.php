<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Factories\{CalculatorFactory, ExtractorFactory};
use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType};
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\ValueObjects\Percent;

#[CalculatorFor(CalculatorType::REQUIRED_PERCENT_DOWN_PAYMENT)]
class RequiredPercentDownPaymentCalculator extends BaseCalculator
{
    public function calculate(): Percent
    {
        // Retrieve original order percent dp
        $original_percent_dp = $this->inputs->order()->getPercentDownPayment();

        // Override the down payment to 0%
        $this->inputs->order()->setPercentDownPayment(0.0);

        $required_equity = CalculatorFactory::make(CalculatorType::EQUITY, $this->inputs)->calculate()->toPrice()
            ->inclusive()
            ->getAmount()
            ->toFloat();
        $tcp = ExtractorFactory::make(ExtractorType::TOTAL_CONTRACT_PRICE, $this->inputs)->extract()
            ->inclusive()
            ->getAmount()
            ->toFloat();

        $percent_dp = ExtractorFactory::make(ExtractorType::PERCENT_DOWN_PAYMENT, $this->inputs)->extract()->value();

        // Suggest percent as next whole percent (e.g., 30.01% → 31%)
        $percent = max(ceil(($required_equity / $tcp) * 100), $percent_dp);

        // Replace with the original percent dp
        $this->inputs->order()->setPercentDownPayment($original_percent_dp);

        return Percent::ofPercent($percent); // ← this handles 31% → 0.31 automatically
    }
}
