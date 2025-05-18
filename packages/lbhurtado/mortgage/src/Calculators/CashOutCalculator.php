<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Factories\{CalculatorFactory, ExtractorFactory, MoneyFactory};
use LBHurtado\Mortgage\Enums\{ExtractorType, CalculatorType};
use LBHurtado\Mortgage\Data\CashOutBreakdownData;
use LBHurtado\Mortgage\ValueObjects\PaymentBreakdown;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
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
            PaymentBreakdown::fromInputs($this->inputs)->amount()
        );
    }

    public function partialMiscellaneousFee(): Price
    {
        return MoneyFactory::priceWithPrecision(
            CalculatorFactory::make(CalculatorType::MISCELLANEOUS_FEES, $this->inputs)->partial()
        );
    }

    public function processingFee(): Price
    {
        return ExtractorFactory::make(ExtractorType::PROCESSING_FEE, $this->inputs)->extract();
//        return $this->inputs->fees->processing_fee;
    }

    public function total(): Price
    {
        return $this->downPayment()->plus($this->partialMiscellaneousFee())->plus($this->processingFee());
    }
}
