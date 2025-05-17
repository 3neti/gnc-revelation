<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Factories\{CalculatorFactory, MoneyFactory};
use LBHurtado\Mortgage\ValueObjects\{DownPayment, FeeCollection};
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Enums\CalculatorType;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::LOAN_AMOUNT)]
final class LoanAmountCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        $loan = DownPayment::fromInputs($this->inputs)->loanable();
        $fees = new FeeCollection(addOns: [
            'miscellaneous fee' => CalculatorFactory::make(CalculatorType::MISCELLANEOUS_FEES, $this->inputs)->toFloat(),
        ]);

        return MoneyFactory::priceWithPrecision($loan->plus($fees->totalAddOns()));
    }

    public function toFloat(): float
    {
        return $this->calculate()->inclusive()->getAmount()->toFloat();
    }
}
