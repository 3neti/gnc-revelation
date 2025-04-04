<?php

namespace App\ValueObjects;

use Brick\Math\RoundingMode;
use Brick\Money\Money;

class DownPayment
{
    protected Money $tcp;
    protected float $percent;

    public function __construct(float $tcp, float $percent)
    {
        $this->tcp = Money::of($tcp, 'PHP');
        $this->percent = $percent;
    }

    public function amount(): Money
    {
        return $this->tcp->multipliedBy($this->percent, roundingMode: RoundingMode::HALF_UP);
    }

    public function loanable(): Money
    {
        return $this->tcp->minus($this->amount());
    }

    public function percent(): float
    {
        return $this->percent;
    }

    public function tcp(): Money
    {
        return $this->tcp;
    }
}
