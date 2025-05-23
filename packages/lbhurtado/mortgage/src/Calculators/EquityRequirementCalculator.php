<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\Enums\CalculatorType;
use LBHurtado\Mortgage\ValueObjects\Equity;

/** TODO: maybe rename to LoanDifferenceCalculator */
#[CalculatorFor(CalculatorType::EQUITY)]
final class EquityRequirementCalculator extends BaseCalculator
{
    public function calculate(): Equity
    {
        $affordableLoan = PresentValueCalculator::fromInputs($this->inputs)
            ->calculate()
            ->inclusive()
            ->getAmount()
            ->toFloat();
        $requiredLoanable = LoanAmountCalculator::fromInputs($this->inputs)
            ->calculate()
            ->inclusive()
            ->getAmount()
            ->toFloat();

        $gap = max(0, $requiredLoanable - $affordableLoan);

        return new Equity(MoneyFactory::priceWithPrecision($gap));
    }

    public function toFloat(): float
    {
        return $this->calculate()->toPrice()->inclusive()->getAmount()->toFloat();
    }
}
