<?php

namespace App\Calculators;

use App\ValueObjects\MiscellaneousFee;
use App\ValueObjects\FeeCollection;
use App\Attributes\CalculatorFor;
use App\ValueObjects\DownPayment;
use App\Enums\CalculatorType;
use App\Support\MoneyFactory;
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
