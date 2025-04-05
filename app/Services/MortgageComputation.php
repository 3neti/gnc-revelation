<?php

namespace App\Services;

use App\Data\Inputs\InputsData;
use App\Data\MonthlyAmortizationBreakdownData;
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
        $principal = $this->getLoanableAmount();
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
        $requiredLoanable = $this->getLoanableAmount();

        $gap = max(0, $requiredLoanable - $affordableLoan);

        return new Equity(MoneyFactory::priceWithPrecision($gap));
    }

    public function getQualificationResult(): QualificationResultData
    {
        $monthly = $this->getMonthlyAmortization();
        $affordableLoan = $this->getPresentValueFromDisposable()->inclusive()->getAmount()->toFloat();
        $requiredLoanable = $this->getLoanableAmount();

        $gap = max(0, $requiredLoanable - $affordableLoan);
        $qualifies = $gap <= 0;
        $suggestedEquity = MoneyFactory::priceWithPrecision($gap);

        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentDp = $this->inputs->loanable->down_payment->percent_dp ?? 0.0;
        $actualDownPayment = $tcp * $percentDp;
        $suggestedDownPercent = ($tcp - $affordableLoan) / $tcp;
        $requiredDownPaymentAmount = $tcp - $affordableLoan;

        $breakdown = $this->getMonthlyAmortizationBreakdown();

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
            required_cash_out: MoneyFactory::priceWithPrecision($this->getCashOut()),
            balance_miscellaneous_fee: MoneyFactory::priceWithPrecision($this->getBalanceMiscellaneousFee()),
            monthly_miscellaneous_fee_share: MoneyFactory::priceWithPrecision($this->getMonthlyMiscellaneousFeeShare()),
            monthly_amortization_breakdown: $breakdown,
        );
    }

//    public function getQualificationResult(): QualificationResultData
//    {
//        $monthly = $this->getMonthlyAmortization();
//        $affordableLoan = $this->getPresentValueFromDisposable()->inclusive()->getAmount()->toFloat();
//        $requiredLoanable = $this->getLoanableAmount();
//
//        $gap = max(0, $requiredLoanable - $affordableLoan);
//        $qualifies = $gap <= 0;
//        $suggestedEquity = MoneyFactory::priceWithPrecision($gap);
//
//        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
//        $percentDp = $this->inputs->loanable->down_payment->percent_dp ?? 0.0;
//        $actualDownPayment = $tcp * $percentDp;
//        $suggestedDownPercent = ($tcp - $affordableLoan) / $tcp;
//        $requiredDownPaymentAmount = $tcp - $affordableLoan;
//
//        return new QualificationResultData(
//            qualifies: $qualifies,
//            gap: $gap,
//            suggested_equity: $suggestedEquity,
//            reason: $qualifies ? 'Qualified' : 'Disposable income too low',
//            monthly_amortization: $monthly,
//            income_required: MoneyFactory::ofWithPrecision($monthly->inclusive()->getAmount()->toFloat() / ($this->inputs->income->income_requirement_multiplier ?? 0.35)),
//            disposable_income: MoneyFactory::ofWithPrecision($this->inputs->income->gross_monthly_income->multipliedBy($this->inputs->income->income_requirement_multiplier ?? 0.35)),
//            suggested_down_payment_percent: $suggestedDownPercent,
//            actual_down_payment: $actualDownPayment,
//            required_loanable: $requiredLoanable,
//            affordable_loanable: $affordableLoan,
//            required_down_payment: MoneyFactory::priceWithPrecision($requiredDownPaymentAmount),
//            required_cash_out: MoneyFactory::priceWithPrecision($this->getCashOut()),
//            balance_miscellaneous_fee: MoneyFactory::priceWithPrecision($this->getBalanceMiscellaneousFee()),
//            monthly_miscellaneous_fee_share: MoneyFactory::priceWithPrecision($this->getMonthlyMiscellaneousFeeShare()),
//        );
//    }

    public function getRequiredLoanableAmount(): float
    {
        return $this->getLoanableAmount();
    }

    public function getBalanceMiscellaneousFee(): float
    {
        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentDp = $this->inputs->loanable->down_payment->percent_dp ?? 0.0;
        $percentMF = $this->inputs->fees->percent_mf ?? 0.0;

        return (1 - $percentDp) * $percentMF * $tcp;
    }

    public function getCashOut(): float
    {
        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentDp = $this->inputs->loanable->down_payment->percent_dp ?? 0.0;
        $percentMF = $this->inputs->fees->percent_mf ?? 0.0;

        return ($percentDp * $tcp) + ($percentDp * $percentMF * $tcp);
    }

    public function getLoanableAmount(): float
    {
        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentDp = $this->inputs->loanable->down_payment->percent_dp ?? 0.0;

        return ($tcp * (1 - $percentDp)) + $this->getBalanceMiscellaneousFee();
    }

    protected function getNetMonthlyAddOns(): Price
    {
        $mri = $this->inputs->monthly_payment_add_ons?->monthly_mri ?? 0.0;
        $fire = $this->inputs->monthly_payment_add_ons?->monthly_fi ?? 0.0;

        return MoneyFactory::priceWithPrecision($mri + $fire);
    }

    public function getMonthlyMiscellaneousFeeShare(): Price
    {
        $balanceMf = $this->getBalanceMiscellaneousFee();
        $months = $this->inputs->balance_payment->bp_term * 12;

        return MoneyFactory::priceWithPrecision($balanceMf / $months);
    }

    public function getPrincipalMonthlyPayment(): Price
    {
        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentDp = $this->inputs->loanable->down_payment->percent_dp ?? 0.0;
        $balancePrincipal = $tcp * (1 - $percentDp);

        $months = $this->inputs->balance_payment->bp_term * 12;
        $monthlyRate = round($this->inputs->balance_payment->bp_interest_rate / 12, 15);

        $payment = $monthlyRate === 0
            ? $balancePrincipal / $months
            : ($balancePrincipal * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));

        return MoneyFactory::priceWithPrecision($payment);
    }

    public function getMonthlyAmortizationBreakdown(): MonthlyAmortizationBreakdownData
    {
        $principal = $this->getPrincipalMonthlyPayment();
        $mf = $this->getMonthlyMiscellaneousFeeShare();
        $addOns = $this->getNetMonthlyAddOns();

        $total = MoneyFactory::priceWithPrecision(
            $principal->inclusive()->getAmount()->toFloat() +
            $mf->inclusive()->getAmount()->toFloat() +
            $addOns->inclusive()->getAmount()->toFloat()
        );

        return new MonthlyAmortizationBreakdownData(
            principal: $principal,
            mf: $mf,
            add_ons: $addOns,
            total: $total
        );
    }
}
