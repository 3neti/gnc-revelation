<?php

namespace App\Data\Inputs;

use App\Contracts\PropertyInterface;
use App\Contracts\OrderInterface;
use App\Contracts\BuyerInterface;
use App\ValueObjects\Percent;
use Spatie\LaravelData\Data;

class BalancePaymentInputsData extends Data
{
    public function __construct(
        public int $bp_term,
        public Percent $bp_interest_rate,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            bp_term: $order->getBalancePaymentTerm() ?? $buyer->getJointMaximumTermAllowed(),
            bp_interest_rate: $order->getInterestRate() ?? ($buyer->getInterestRate() ?? $property->getInterestRate())
        );
    }
}
