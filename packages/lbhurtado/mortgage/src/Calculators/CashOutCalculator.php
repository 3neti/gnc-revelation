<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\ValueObjects\{DownPayment, MiscellaneousFee};
use LBHurtado\Mortgage\Data\CashOutBreakdownData;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\Enums\CalculatorType;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::CASH_OUT)]
final class CashOutCalculator extends BaseCalculator
{
    public function calculate(): CashOutBreakdownData
    {
        return new CashOutBreakdownData(
            down_payment: $this->downPayment(),
            miscellaneous_fee: $this->partialMiscellaneousFee(),
            processing_fee: $this->processingFee(),
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

    public function processingFee(): Price
    {
        return $this->inputs->fees->processing_fee;
    }

    public function total(): Price
    {
        return $this->downPayment()->plus($this->partialMiscellaneousFee())->plus($this->processingFee());
    }
}
