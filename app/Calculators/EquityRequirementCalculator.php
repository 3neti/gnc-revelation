<?php

namespace App\Calculators;

use App\Attributes\CalculatorFor;
use App\Enums\CalculatorType;
use App\Support\MoneyFactory;
use App\ValueObjects\Equity;

#[CalculatorFor(CalculatorType::EQUITY)]
final class EquityRequirementCalculator extends BaseCalculator
{
    public function calculate(): Equity
    {
        $affordableLoan = LoanAffordabilityCalculator::fromInputs($this->inputs)
            ->calculate()
            ->inclusive()
            ->getAmount()
            ->toFloat();
        $requiredLoanable = LoanableAmountCalculator::fromInputs($this->inputs)
            ->calculate()
            ->inclusive()
            ->getAmount()
            ->toFloat();

        $gap = max(0, $requiredLoanable - $affordableLoan);
//        dd($requiredLoanable, $affordableLoan, $gap);
        return new Equity(MoneyFactory::priceWithPrecision($gap));
    }
}
