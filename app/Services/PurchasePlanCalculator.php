<?php

namespace App\Services;

use App\DataObjects\QualificationResult;
use App\Services\Mortgage\PresentValue;
use App\DataObjects\MortgageTerm;
use Brick\Math\RoundingMode;
use Jarouche\Financial\PMT;
use Whitecube\Price\Price;
use Brick\Money\Money;

class PurchasePlanCalculator
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

        $totalAddOn = array_reduce($this->addOnFees, fn($carry, Money $fee) => $carry->plus($fee), Money::of(0, 'PHP'));
        $totalDeductible = array_reduce($this->deductibleFees, fn($carry, Money $fee) => $carry->plus($fee), Money::of(0, 'PHP'));

        return (new Price($monthly))
            ->addModifier('add-ons', $totalAddOn)
            ->addModifier('deductibles', fn($modifier) => $modifier->subtract($totalDeductible));
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
        $pv = new \Jarouche\Financial\PV($this->interestRate / 12, $this->term->months(), $pmt);
        $value = round($pv->evaluate(), 2);

        return new Price(
            Money::of($value, 'PHP', roundingMode: RoundingMode::CEILING)
        );
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
            ->setPayment($actualDisposable->getAmount()->toFloat())
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
}
