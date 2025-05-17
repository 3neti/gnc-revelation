<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\ValueObjects\{DownPayment, FeeCollection, MiscellaneousFee};
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Enums\ExtractorType;
use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\Factories\MoneyFactory;
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
//dd('LoanAmountCalculator', $loan->getAmount()->toFloat(), $fees->totalAddOns()->getAmount()->toFloat(), 1400000 * 1.085);
        return MoneyFactory::priceWithPrecision($loan->plus($fees->totalAddOns()));
    }

    public function toFloat(): float
    {
        return $this->calculate()->inclusive()->getAmount()->toFloat();
    }
}
