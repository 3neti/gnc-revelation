<?php

namespace App\Services;

use App\ValueObjects\{DownPayment, Equity, FeeCollection};
use App\Data\QualificationResultData;
use App\DataObjects\MortgageTerm;
use Brick\Math\RoundingMode;
use Whitecube\Price\Price;
use Brick\Money\Money;

class PurchasePlanCalculator
{
    public function __construct(
        public DownPayment $downPayment,
        public float $interestRate,
        public MortgageTerm $term,
        public FeeCollection $fees = new FeeCollection(),
        public float $disposableMultiplier = 0.35,
    ) {}

    public function monthlyAmortization(): Price
    {
        $loanable = $this->downPayment->loanable()->getAmount()->toFloat();
        $months = $this->term->months();
        $monthlyRate = round($this->interestRate / 12, 15);

        $baseMonthly = $monthlyRate === 0
            ? $loanable / $months
            : ($loanable * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));

        $netFees = $this->fees->netFees()->getAmount()->toFloat();
        $netMonthly = $baseMonthly + $netFees;

        return new Price(Money::of(round($netMonthly, 2), 'PHP'));
    }

    public function incomeRequirement(): Money
    {
        return $this->monthlyAmortization()
            ->inclusive()
            ->dividedBy($this->disposableMultiplier, roundingMode: RoundingMode::HALF_UP);
    }

    public function presentValue(): Price
    {
        $monthly = $this->monthlyAmortization()->inclusive()->getAmount()->toFloat();
        $monthlyRate = $this->interestRate / 12;
        $months = $this->term->months();

        $pv = $monthly * ((1 - pow(1 + $monthlyRate, -$months)) / $monthlyRate);

        return new Price(Money::of(round($pv, 2), 'PHP'));
    }

    public function computeRequiredEquity(Money $actualDisposable): Equity
    {
        $monthly = $this->monthlyAmortization()->inclusive()->getAmount()->toFloat();
        $actual = $actualDisposable->getAmount()->toFloat();
        $months = $this->term->months();
        $monthlyRate = $this->interestRate / 12;
        $netFees = $this->fees->netFees()->getAmount()->toFloat();

        $affordableMonthly = max(0, $actual - $netFees);

        $affordableLoan = $monthlyRate === 0
            ? $affordableMonthly * $months
            : $affordableMonthly * (1 - pow(1 + $monthlyRate, -$months)) / $monthlyRate;

        $suggestedLoanable = Money::of(round($affordableLoan, 2), 'PHP');

        return Equity::fromRequired($this->downPayment->tcp(), $suggestedLoanable);
    }

//    public function computeRequiredEquity(Money $actualDisposable): Price
//    {
//        $monthly = $this->monthlyAmortization()->inclusive()->getAmount()->toFloat();
//        $actual = $actualDisposable->getAmount()->toFloat();
//        $months = $this->term->months();
//        $monthlyRate = $this->interestRate / 12;
//        $netFees = $this->fees->netFees()->getAmount()->toFloat();
//
//        // Compute the most the borrower can afford in monthly payment
//        $affordableMonthly = max(0, $actual - $netFees);
//
//        // Compute the affordable loanable amount
//        $affordableLoan = $monthlyRate === 0
//            ? $affordableMonthly * $months
//            : $affordableMonthly * (1 - pow(1 + $monthlyRate, -$months)) / $monthlyRate;
//
//        $suggestedLoanable = Money::of(round($affordableLoan, 2), 'PHP');
//        $requiredEquity = $this->downPayment->tcp()->minus($suggestedLoanable);
//
//        // 💡 Always return a positive equity suggestion, never negative
//        return new Price(Money::of(max(0, $requiredEquity->getAmount()->toFloat()), 'PHP'));
//    }

    public function getQualificationResultFromGrossIncome(float $grossIncome): QualificationResultData
    {
        $actualDisposable = Money::of($grossIncome * $this->disposableMultiplier, 'PHP');
        return $this->getQualificationResult($actualDisposable);
    }

    public function getQualificationResult(Money $actualDisposable): QualificationResultData
    {
        $amort = $this->monthlyAmortization();
        $incomeRequired = $this->incomeRequirement();

        $qualifies = $actualDisposable->isGreaterThanOrEqualTo($amort->inclusive());
        $gap = max(0, round($amort->inclusive()->getAmount()->toFloat() - $actualDisposable->getAmount()->toFloat(), 2));

        return new QualificationResultData(
            qualifies: $qualifies,
            gap: $gap,
            suggested_equity: $this->computeRequiredEquity($actualDisposable),
            reason: $qualifies ? 'Sufficient disposable income' : 'Disposable income below amortization',
            monthly_amortization: $amort,
            income_required: $incomeRequired,
            disposable_income: $actualDisposable
        );
    }
}
