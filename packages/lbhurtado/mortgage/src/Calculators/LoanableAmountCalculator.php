<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\ValueObjects\{DownPayment, FeeCollection, MiscellaneousFee};
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\Enums\CalculatorType;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::LOANABLE_AMOUNT)]
final class LoanableAmountCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        $loanable = DownPayment::fromInputs($this->inputs)->loanable();
        $fees = new FeeCollection(addOns: [
            'balance miscellaneous fee' => MiscellaneousFee::fromInputs($this->inputs)->balance()->getAmount()->toFloat(),
        ]);

        return MoneyFactory::priceWithPrecision($loanable->plus($fees->totalAddOns()));
    }
}
