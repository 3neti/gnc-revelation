<?php

namespace App\Services;

use App\DataObjects\QualificationResult;
use App\DataObjects\MortgageTerm;
use App\Services\Mortgage\PresentValue;
use Brick\Math\RoundingMode;
use Jarouche\Financial\PMT;
use Jarouche\Financial\PV;
use Whitecube\Price\Price;
use Brick\Money\Money;

class MortgageCalculatorService
{
    public function __construct(
        protected float $principal,
        protected float $interestRate,
        protected MortgageTerm $term,
        protected float $disposableMultiplier = 0.30,
        protected array $addOnFees = [],
        protected array $deductibleFees = [],
    ) {}

    public function monthlyAmortization(): Price
    {
        $monthlyInterestRate = $this->interestRate / 12;
        $n = $this->term->months();
        $base = ($monthlyInterestRate > 0)
            ? (new PMT($monthlyInterestRate, $n, $this->principal))->evaluate()
            : $this->principal / $n;

        $monthly = Money::of($base, 'PHP', roundingMode: RoundingMode::CEILING);

        // Add-on fees
        $totalAddOn = array_reduce($this->addOnFees, fn ($carry, Money $fee) => $carry->plus($fee), Money::of(0, 'PHP'));

        // Deductibles
        $totalDeductible = array_reduce($this->deductibleFees, fn ($carry, Money $fee) => $carry->plus($fee), Money::of(0, 'PHP'));

        return (new Price($monthly))
            ->addModifier('add-ons', $totalAddOn)
            ->addModifier('deductibles', fn ($modifier) => $modifier->subtract($totalDeductible));
    }

    public function incomeRequirement(): Money
    {
        return $this->monthlyAmortization()
            ->inclusive()
            ->dividedBy($this->disposableMultiplier, roundingMode: RoundingMode::CEILING);
    }

    public function presentValue(): Price
    {
        $pmt = $this->monthlyAmortization()->inclusive()->getAmount()->toFloat();
        $pv = new PV($this->interestRate / 12, $this->term->months(), $pmt);

        return new Price(Money::of($pv->evaluate(), 'PHP', roundingMode: RoundingMode::CEILING));
    }

    public function addAddOnFee(string $label, float $amount): static
    {
        $this->addOnFees[$label] = Money::of($amount, 'PHP');
        return $this;
    }

    public function addDeductibleFee(string $label, float $amount): static
    {
        $this->deductibleFees[$label] = Money::of($amount, 'PHP');
        return $this;
    }

    public function computeRequiredEquity(Money $actualDisposable): Price
    {
        $presentValue = (new PresentValue)
            ->setPayment($actualDisposable->getAmount()->toFloat()) // ← FIXED
            ->setTerm($this->term)
            ->setInterestRate($this->interestRate)
            ->getDiscountedValue();

        $diff = max(0, $this->principal - $presentValue->inclusive()->getAmount()->toFloat());

        return new Price(
            Money::of($diff, 'PHP', roundingMode: RoundingMode::CEILING)
        );
    }

    public function getQualificationResult(Money $actualDisposable): QualificationResult
    {
        $amort = $this->monthlyAmortization();
        $actual = $actualDisposable;

        $amortValue = $amort->inclusive()->getAmount()->toFloat();
        $disposableValue = $actual->getAmount()->toFloat();

        $gap = max(0, $amortValue - $disposableValue);
        $qualifies = $gap <= 0;
        $equity = $this->computeRequiredEquity($actual);

        return new QualificationResult(
            qualifies: $qualifies,
            gap: round($gap, 2),
            suggested_equity: $equity,
            reason: $qualifies ? 'Sufficient disposable income' : 'Disposable income below amortization',
            monthly_amortization: $amort,
            income_required: $this->incomeRequirement(),
            disposable_income: $actual
        );
    }

    public function computeMonthlyAmortization(): float
    {
        $rate = $this->interestRate / 12;
        $months = $this->term->months();
        $pmt = new PMT($rate, $months, $this->principal);

        return round($pmt->evaluate(), 2);
    }
}
