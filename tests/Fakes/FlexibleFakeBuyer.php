<?php

namespace Tests\Fakes;

use App\Contracts\BuyerInterface;
use Whitecube\Price\Price;

/** @deprecated  */
class FlexibleFakeBuyer implements BuyerInterface
{
    public function __construct(
        protected float  $gross_monthly_income,
        protected int    $joint_maximum_term_allowed,
        protected ?float $interest_rate = null,
        protected ?int   $down_payment_term = null,
        protected ?int   $balance_payment_term = null,
        protected ?float $joint_monthly_disposable_income = null,
    ) {}

    public function getMonthlyGrossIncome(): Price
    {
        return Price::of($this->gross_monthly_income, 'PHP');
    }

    public function getJointMonthlyDisposableIncome(): Price
    {
        return Price::of($this->joint_monthly_disposable_income, 'PHP');
    }

    public function getJointMaximumTermAllowed(): int
    {
        return $this->joint_maximum_term_allowed;
    }

    public function getInterestRate(): ?float
    {
        return $this->interest_rate;
    }

    public function getDownPaymentTerm(): ?int
    {
        return $this->down_payment_term;
    }

    public function getBalancePaymentTerm(): ?int
    {
        return $this->balance_payment_term;
    }
}
