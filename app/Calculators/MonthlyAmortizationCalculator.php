<?php

namespace App\Calculators;

use App\Data\MonthlyAmortizationBreakdownData;
use App\Modifiers\PeriodicPaymentModifier;
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
            mf: $this->monthlyMiscFee(),
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

    public function monthlyMiscFee(): Price
    {
        $tcp = $this->getTotalContractPriceInput();
        $percent_dp = $this->getPercentDownPaymentInput();
        $percentMF = $this->inputs->fees->percent_mf?->value() ?? 0.0;

        $balanceMF = (1 - $percent_dp) * $percentMF * $tcp;
        $months = $this->getBalancePaymentTermInputInMonths();

        return MoneyFactory::priceWithPrecision($balanceMF / $months);
    }

    public function addOns(): Price
    {
        $mri = $this->inputs->monthly_payment_add_ons->monthly_mri ?? 0.0;
        $fire = $this->inputs->monthly_payment_add_ons->monthly_fi ?? 0.0;

        return MoneyFactory::priceWithPrecision($mri + $fire);
    }

    public function total(): Price
    {
        return Price::addMany([
            $this->principal(),
            $this->monthlyMiscFee(),
            $this->addOns()
        ]);
    }

    protected function getTotalContractPriceInput(): float
    {
        return $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
    }

    protected function getPercentDownPaymentInput(): float
    {
        return $this->inputs->loanable->down_payment->percent_dp?->value() ?? 0.0;
    }

    protected function getBalancePaymentTermInputInMonths(): int|float
    {
        return $this->inputs->balance_payment->bp_term * 12;
    }

    protected function getBalancePaymentInterestRateInMonths(): float
    {
        return round($this->inputs->balance_payment->bp_interest_rate->value() / 12, 15);
    }

    protected function isZeroInterest(float $rate): bool
    {
        return abs($rate) < 1e-10;
    }
}
