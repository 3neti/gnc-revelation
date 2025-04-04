<?php

namespace App\ValueObjects;

use App\Support\MoneyFactory;
use Whitecube\Price\Price;

final class Equity
{
    public function __construct(
        public readonly Price $amount
    ) {}

    public static function zero(): self
    {
        return new self(MoneyFactory::priceZero());
    }

    public function isZero(): bool
    {
        return $this->amount->inclusive()->isZero();
    }

    public function greaterThan(Price $other): bool
    {
        return $this->amount->inclusive()->isGreaterThan($other->inclusive());
    }

    public function asPercentOf(Price $base): float
    {
        if ($base->inclusive()->isZero()) {
            return 0.0;
        }

        $equity = $this->amount->inclusive()->getAmount()->toFloat();
        $baseAmount = $base->inclusive()->getAmount()->toFloat();

        return round(($equity / $baseAmount) * 100, 2);
    }

    public function toPrice(): Price
    {
        return $this->amount;
    }
}
