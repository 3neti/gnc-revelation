<?php

namespace App\Calculators;

use App\ValueObjects\{DownPayment, MiscellaneousFee};
use App\Data\CashOutBreakdownData;
use App\Attributes\CalculatorFor;
use App\Enums\CalculatorType;
use App\Support\MoneyFactory;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::CASH_OUT)]
final class CashOutCalculator extends BaseCalculator
{
    public function calculate(): CashOutBreakdownData
    {
        return new CashOutBreakdownData(
            down_payment: $this->downPayment(),
            miscellaneous_fee: $this->partialMiscellaneousFee(),
            total: $this->total()
        );
    }

    public function downPayment(): Price
    {
        return MoneyFactory::priceWithPrecision(
            DownPayment::fromInputs($this->inputs)->amount()
        );
    }

    public function partialMiscellaneousFee(): Price
    {
        return MoneyFactory::priceWithPrecision(
            MiscellaneousFee::fromInputs($this->inputs)->partial()
        );
    }

    public function total(): Price
    {
        return $this->downPayment()->plus($this->partialMiscellaneousFee());
    }
}
