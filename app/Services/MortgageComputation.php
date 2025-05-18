<?php

namespace App\Services;

use App\Data\QualificationResultData;
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\Enums\CalculatorType;
use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use Whitecube\Price\Price;

final class MortgageComputation
{
    public function __construct(public MortgageParticulars $inputs) {}

    public static function fromInputs(MortgageParticulars $inputs): self
    {
        return new self($inputs);
    }

    public function getQualificationResult(): QualificationResultData
    {
        // TODO: Delegate to CalculatorFactory where applicable

        // Temporarily using inline calculations until refactors are complete
        $monthly = CalculatorFactory::make(CalculatorType::AMORTIZATION, $this->inputs)->calculate()->principal;
        $affordableLoan = CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $this->inputs)->calculate()->inclusive()->getAmount()->toFloat();
        $disposable = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $this->inputs)->calculate();
        $requiredLoanable = $this->getLoanableAmount();

        $gap = max(0, $requiredLoanable - $affordableLoan);
        $qualifies = $gap <= 0;
        $suggestedEquity = MoneyFactory::priceWithPrecision($gap);

        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentDp = $this->inputs->loanable->down_payment->percent_dp?->value() ?? 0.0;
        $actualDownPayment = $tcp * $percentDp;
        $suggestedDownPercent = ($tcp - $affordableLoan) / $tcp;
        $requiredDownPaymentAmount = $tcp - $affordableLoan;

        $breakdown = CalculatorFactory::make(CalculatorType::AMORTIZATION, $this->inputs)->calculate();

        return new QualificationResultData(
            qualifies: $qualifies,
            gap: $gap,
            suggested_equity: $suggestedEquity,
            reason: $qualifies ? 'Qualified' : 'Disposable income too low',
            monthly_amortization: $monthly,
            income_required: MoneyFactory::ofWithPrecision($monthly->inclusive()->getAmount()->toFloat() / ($this->inputs->income->income_requirement_multiplier?->value() ?? 0.35)),
            disposable_income: $disposable,
            suggested_down_payment_percent: $suggestedDownPercent,
            actual_down_payment: $actualDownPayment,
            required_loanable: $requiredLoanable,
            affordable_loanable: $affordableLoan,
            required_down_payment: MoneyFactory::priceWithPrecision($requiredDownPaymentAmount),
            required_cash_out: CalculatorFactory::make(CalculatorType::CASH_OUT, $this->inputs)->calculate(),
            balance_miscellaneous_fee: MoneyFactory::priceWithPrecision($this->getBalanceMiscellaneousFee()), // TODO
            monthly_miscellaneous_fee_share: MoneyFactory::priceWithPrecision($this->getBalanceMiscellaneousFee() / ($this->inputs->balance_payment->bp_term * 12)), // TODO
            monthly_amortization_breakdown: $breakdown,
        );
    }

    protected function getBalanceMiscellaneousFee(): float
    {
        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentDp = $this->inputs->loanable->down_payment->percent_dp?->value() ?? 0.0;
        $percentMF = $this->inputs->fees->percent_mf?->value() ?? 0.0;

        return (1 - $percentDp) * $percentMF * $tcp;
    }

    protected function getNetMonthlyAddOns(): Price
    {
        $mri = $this->inputs->monthly_payment_add_ons->monthly_mri ?? 0.0;
        $fire = $this->inputs->monthly_payment_add_ons->monthly_fi ?? 0.0;

        return MoneyFactory::priceWithPrecision($mri + $fire);
    }

    // TODO: Consider refactoring this class to only aggregate calculator results
}
