<?php

namespace App\Data\Inputs;

use App\Contracts\PropertyInterface;
use App\Contracts\OrderInterface;
use App\Contracts\BuyerInterface;
use Spatie\LaravelData\Data;

class DownPaymentInputsData extends Data
{
    public function __construct(
        public ?float $percent_dp = null,
        public ?int $dp_term  = null,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            percent_dp: $order->getPercentDownPayment(),
            dp_term: $buyer->getDownPaymentTerm() && $order->getDownPaymentTerm()
                ? min($buyer->getDownPaymentTerm(), $order->getDownPaymentTerm())
                : ($buyer->getDownPaymentTerm() ?? $order->getDownPaymentTerm()),
        );
    }
}
