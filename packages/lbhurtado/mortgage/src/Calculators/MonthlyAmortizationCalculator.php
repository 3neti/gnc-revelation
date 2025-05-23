<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Data\MonthlyAmortizationBreakdownData;
use LBHurtado\Mortgage\Enums\ExtractorType;
use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\Modifiers\PeriodicPaymentModifier;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\Enums\CalculatorType;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::AMORTIZATION)]
final class MonthlyAmortizationCalculator extends BaseCalculator
{
    public function calculate(): MonthlyAmortizationBreakdownData
    {
        return new MonthlyAmortizationBreakdownData(
            principal: $this->principal(),
            add_ons: $this->addOns()
        );
    }

    public function principal(): Price
    {
        $term = $this->getBalancePaymentTermInputInMonths();
        $rate = $this->getBalancePaymentInterestRateInMonths();
        $ma = LoanAmountCalculator::fromInputs($this->inputs)
            ->calculate()
            ->addModifier('periodic payment', PeriodicPaymentModifier::class, $term, $rate)
            ->inclusive();

        return MoneyFactory::priceWithPrecision($ma);
    }

    public function addOns(): Price
    {
        return FeesCalculator::fromInputs($this->inputs)->total();
    }

    public function total(): Price
    {
        return $this->principal()
            ->plus($this->addOns());
    }

    protected function getBalancePaymentTermInputInMonths(): int|float
    {
        $balance_payment_term = CalculatorFactory::make(CalculatorType::BALANCE_PAYMENT_TERM, $this->inputs)->calculate();

        return $balance_payment_term * 12;
    }

    protected function getBalancePaymentInterestRateInMonths(): float
    {
        $balance_payment_interest_rate = ExtractorFactory::make(ExtractorType::INTEREST_RATE, $this->inputs)->extract()->value();

        return round($balance_payment_interest_rate / 12, 15);
//        return round($this->inputs->balance_payment->bp_interest_rate->value() / 12, 15);
    }

//    public function monthlyMiscFeeContribution(): Price
//    {
//        $months = $this->getBalancePaymentTermInputInMonths();
//        $mf = MiscellaneousFee::fromInputs($this->inputs)->balance();
//
//        return MoneyFactory::priceWithPrecision($mf->getAmount()->toFloat() / $months);
//    }
}
