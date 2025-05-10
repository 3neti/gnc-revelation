<?php

namespace LBHurtado\Mortgage\Data\Inputs;

use LBHurtado\Mortgage\Contracts\{BuyerInterface, OrderInterface, PropertyInterface};
use LBHurtado\Mortgage\ValueObjects\Percent;
use Spatie\LaravelData\Data;

/** TODO: deprecate this */
class BalancePaymentInputsData extends Data
{
    public function __construct(
        public int $bp_term,
        public Percent $bp_interest_rate,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            bp_term: 0,
            bp_interest_rate: $order->getInterestRate() ?? ($buyer->getInterestRate() ?? $property->getInterestRate())
        );
//        return new static(
//            bp_term: $order->getBalancePaymentTerm() ?? $buyer->getJointMaximumTermAllowed(),
//            bp_interest_rate: $order->getInterestRate() ?? ($buyer->getInterestRate() ?? $property->getInterestRate())
//        );
    }
}
