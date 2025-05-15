<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Exceptions\IncomeRequirementMultiplierNotSetException;
use LBHurtado\Mortgage\Factories\{CalculatorFactory, ExtractorFactory};
use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType};
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use App\Modifiers\PresentValueModifier;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::PRESENT_VALUE)]
final class PresentValueCalculator extends BaseCalculator
{
    /**
     * @throws IncomeRequirementMultiplierNotSetException|\ReflectionException
     */
    public function calculate(): Price
    {
        $term = CalculatorFactory::make(CalculatorType::BALANCE_PAYMENT_TERM, $this->inputs)->calculate();
        $interest_rate = ExtractorFactory::make(ExtractorType::INTEREST_RATE, $this->inputs)->extract()->value();
        $present_value = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $this->inputs)->calculate()
            ->addModifier('present value', PresentValueModifier::class, $term, $interest_rate)
            ->inclusive();

        return MoneyFactory::price($present_value);
    }
}
