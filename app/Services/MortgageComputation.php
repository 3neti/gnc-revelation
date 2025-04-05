<?php

namespace App\Services;

use App\Data\Inputs\InputsData;
use App\Data\QualificationResultData;
use App\Support\MoneyFactory;
use App\ValueObjects\Equity;
use Whitecube\Price\Price;
use Brick\Money\Money;

final class MortgageComputation
{
    public function __construct(public InputsData $inputs) {}

    public static function fromInputs(InputsData $inputs): self
    {
        return new self($inputs);
    }

    public function getMonthlyAmortization(): Price
    {
        $principal = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $months = $this->inputs->balance_payment->bp_term * 12;
        $monthlyRate = round($this->inputs->balance_payment->bp_interest_rate / 12, 15);

        $baseMonthly = $monthlyRate === 0
            ? $principal / $months
            : ($principal * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));

        $addOns = $this->getNetMonthlyAddOns()->inclusive()->getAmount()->toFloat();

        return MoneyFactory::priceWithPrecision($baseMonthly + $addOns);
    }

    public function getPresentValueFromDisposable(): Price
    {
        $multiplier = $this->inputs->income->income_requirement_multiplier ?? 0.35;
        $grossIncome = $this->inputs->income->gross_monthly_income;
        $actualDisposable = $grossIncome->multipliedBy($multiplier);

        $netFees = $this->getNetMonthlyAddOns()->inclusive()->getAmount()->toFloat();
        $affordableMonthly = max(0, $actualDisposable->getAmount()->toFloat() - $netFees);

        $months = $this->inputs->balance_payment->bp_term * 12;
        $monthlyRate = round($this->inputs->balance_payment->bp_interest_rate / 12, 15);

        $presentValue = $monthlyRate === 0
            ? $affordableMonthly * $months
            : $affordableMonthly * (1 - pow(1 + $monthlyRate, -$months)) / $monthlyRate;

        return MoneyFactory::priceWithPrecision(round($presentValue, 2));
    }

    public function computeRequiredEquity(): Equity
    {
        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentDp = $this->inputs->loanable->down_payment->percent_dp ?? 0.0;
        $actualDownpayment = $tcp * $percentDp;

        $affordableLoan = $this->getPresentValueFromDisposable()->inclusive()->getAmount()->toFloat();
        $requiredLoanable = $tcp - $actualDownpayment;

        $gap = max(0, $requiredLoanable - $affordableLoan);

        return new Equity(MoneyFactory::priceWithPrecision($gap));
    }

    public function getQualificationResult(): QualificationResultData
    {
        $monthly = $this->getMonthlyAmortization();
        $affordableLoan = $this->getPresentValueFromDisposable()->inclusive()->getAmount()->toFloat();
        $requiredLoanable = $this->getRequiredLoanableAmount();

        $gap = max(0, $requiredLoanable - $affordableLoan);
        $qualifies = $gap <= 0;
        $suggestedEquity = MoneyFactory::priceWithPrecision($gap);

        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $actualDownPayment = $this->inputs->loanable->down_payment->percent_dp * $tcp;
        $suggestedDownPercent = ($tcp - $affordableLoan) / $tcp;
        $requiredDownPaymentAmount = $tcp - $affordableLoan;

        return new QualificationResultData(
            qualifies: $qualifies,
            gap: $gap,
            suggested_equity: $suggestedEquity,
            reason: $qualifies ? 'Qualified' : 'Disposable income too low',
            monthly_amortization: $monthly,
            income_required: MoneyFactory::ofWithPrecision($monthly->inclusive()->getAmount()->toFloat() / ($this->inputs->income->income_requirement_multiplier ?? 0.35)),
            disposable_income: MoneyFactory::ofWithPrecision($this->inputs->income->gross_monthly_income->multipliedBy($this->inputs->income->income_requirement_multiplier ?? 0.35)),
            suggested_down_payment_percent: $suggestedDownPercent,
            actual_down_payment: $actualDownPayment,
            required_loanable: $requiredLoanable,
            affordable_loanable: $affordableLoan,
            required_down_payment: MoneyFactory::priceWithPrecision($requiredDownPaymentAmount),
        );
    }

    public function getRequiredLoanableAmount(): float
    {
        return $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat()
            - ($this->inputs->loanable->down_payment->percent_dp ?? 0.0)
            * $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
    }

    protected function getNetMonthlyAddOns(): Price
    {
        $mri = $this->inputs->monthly_payment_add_ons?->mortgage_redemption_insurance ?? 0.0;
        $fire = $this->inputs->monthly_payment_add_ons?->annual_fire_insurance ?? 0.0;

        return MoneyFactory::priceWithPrecision($mri + $fire);
    }
}
