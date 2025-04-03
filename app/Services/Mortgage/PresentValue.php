<?php

namespace App\Services\Mortgage;

use App\DataObjects\MortgageTerm;
use Brick\Math\RoundingMode;
use Whitecube\Price\Price;
use Jarouche\Financial\PV;
use Brick\Money\Money;

class PresentValue
{
    protected float $payment;
    protected float $interestRate; // annual
    protected MortgageTerm $term;

    public function setPayment(float $payment): self
    {
        $this->payment = $payment;
        return $this;
    }

    public function setInterestRate(float $rate): self
    {
        $this->interestRate = $rate;
        return $this;
    }

    public function setTerm(MortgageTerm $term): self
    {
        $this->term = $term;
        return $this;
    }

    public function getDiscountedValue(): Price
    {
        $monthlyRate = $this->interestRate / 12;
        $months = $this->term->monthsToPay();

        $pv = new PV($monthlyRate, $months, $this->payment);
        $value = round($pv->evaluate(), 2);

        return new Price(
            Money::of($value, 'PHP', roundingMode: RoundingMode::CEILING)
        );
    }
}
