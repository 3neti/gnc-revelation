<?php

namespace LBHurtado\Mortgage\Data;

use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Contracts\BuyerInterface;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;
use Brick\Money\Money;

class QualificationComputationData extends Data
{
    public function __construct(
        public Price   $loanable,
        public Percent $interest,
        public int     $termYears,
        public float   $monthlyInterestRate,
        public Money   $monthlyPayment,
        public Money   $required,
        public Money   $actual,
        public Percent $buffer,
    ) {}

    public static function fromBuyerAndProperty(BuyerInterface $buyer, PropertyInterface $property): self
    {
        $buffer    = $buyer->resolveBufferMargin($property); // float
        $loanable  = $property->getLoanableAmount(); // Price
        $interest  = $property->getInterestRate(); // Percent
        $termYears = $buyer->getJointMaximumTermAllowed();

        $monthlyInterestRate = $interest->value() / 12;
        $numberOfMonths      = $termYears * 12;

        $monthlyPaymentFloat = (
                $loanable->inclusive()->getAmount()->toFloat() * $monthlyInterestRate
            ) / (
                1 - pow(1 + $monthlyInterestRate, -$numberOfMonths)
            );

        $monthlyPayment = Money::of($monthlyPaymentFloat, 'PHP', roundingMode: \Brick\Math\RoundingMode::HALF_UP);
        $required       = $monthlyPayment->multipliedBy(1 + $buffer, \Brick\Math\RoundingMode::HALF_UP);
        $actual         = $buyer->getJointMonthlyDisposableIncome()->inclusive();

        return new self(
            loanable: $loanable,
            interest: $interest,
            termYears: $termYears,
            monthlyInterestRate: $monthlyInterestRate,
            monthlyPayment: $monthlyPayment,
            required: $required,
            actual: $actual,
            buffer: Percent::ofFraction($buffer),
        );
    }

    public function qualifies(): bool
    {
        return $this->actual->isGreaterThanOrEqualTo($this->required);
    }

    public function gap(): Money
    {
        return $this->qualifies()
            ? Money::of(0, 'PHP')
            : $this->required->minus($this->actual);
    }

    public function failedMessage(): ?string
    {
        if ($this->qualifies()) {
            return null;
        }

        return 'You need at least ₱' . number_format($this->gap()->getAmount()->toFloat(), 2) . ' more in joint disposable income to qualify.';
    }
}
