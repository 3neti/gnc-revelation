<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Exceptions\IncomeRequirementMultiplierNotSetException;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\Enums\CalculatorType;
use App\Modifiers\PresentValueModifier;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::PRESENT_VALUE)]
final class LoanAffordabilityCalculator extends BaseCalculator
{
    /**
     * @throws IncomeRequirementMultiplierNotSetException
     */
    public function calculate(): Price
    {
        $term = $this->inputs->balance_payment->bp_term;
        $interest_rate = $this->inputs->balance_payment->bp_interest_rate->value();
        $present_value = MonthlyDisposableIncomeCalculator::fromInputs($this->inputs)
            ->calculate()
            ->addModifier('present value', PresentValueModifier::class, $term, $interest_rate)
            ->inclusive();

        return MoneyFactory::price($present_value);
    }
}
