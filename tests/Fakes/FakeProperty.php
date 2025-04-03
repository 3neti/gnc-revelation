<?php

namespace Tests\Fakes;

use App\Contracts\PropertyInterface;
use Whitecube\Price\Price;
use Brick\Money\Money;

class FakeProperty implements PropertyInterface
{
    public function getLoanableAmount(): Price
    {
        return new Price(Money::of(1000000, 'PHP')); // ₱1M loan
    }

    public function getInterestRate(): float
    {
        return 0.06; // 6% annual interest
    }

    public function getRequiredBufferMargin(): ?float
    {
        return 0.1;
    }
}
