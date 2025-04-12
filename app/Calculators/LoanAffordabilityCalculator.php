<?php

namespace App\Calculators;

use App\Exceptions\IncomeRequirementMultiplierNotSetException;
use App\Modifiers\PresentValueModifier;
use App\Attributes\CalculatorFor;
use App\Enums\CalculatorType;
use App\Support\MoneyFactory;
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
