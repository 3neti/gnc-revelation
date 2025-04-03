<?php

namespace Tests\Fakes;

use App\Contracts\PropertyInterface;
use Whitecube\Price\Price;
use Brick\Money\Money;

class FlexibleFakeProperty implements PropertyInterface
{
    public function __construct(
        protected float $loanable,
        protected float $interest,
        protected ?float $required_buffer_margin = 0.1,
    ) {}

    public function getLoanableAmount(): Price
    {
        return new Price(Money::of($this->loanable, 'PHP'));
    }

    public function getInterestRate(): float
    {
        return $this->interest;
    }

    public function getRequiredBufferMargin(): ?float
    {
       return $this->required_buffer_margin;
    }
}
