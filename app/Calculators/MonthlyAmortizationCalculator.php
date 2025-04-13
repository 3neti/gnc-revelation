<?php

namespace App\Calculators;

use App\Data\MonthlyAmortizationBreakdownData;
use App\Modifiers\PeriodicPaymentModifier;
use App\ValueObjects\MiscellaneousFee;
use App\Attributes\CalculatorFor;
use App\Support\MoneyFactory;
use App\Enums\CalculatorType;
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
        $ma = LoanableAmountCalculator::fromInputs($this->inputs)
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
        return $this->inputs->balance_payment->bp_term * 12;
    }

    protected function getBalancePaymentInterestRateInMonths(): float
    {
        return round($this->inputs->balance_payment->bp_interest_rate->value() / 12, 15);
    }

//    public function monthlyMiscFeeContribution(): Price
//    {
//        $months = $this->getBalancePaymentTermInputInMonths();
//        $mf = MiscellaneousFee::fromInputs($this->inputs)->balance();
//
//        return MoneyFactory::priceWithPrecision($mf->getAmount()->toFloat() / $months);
//    }
}
