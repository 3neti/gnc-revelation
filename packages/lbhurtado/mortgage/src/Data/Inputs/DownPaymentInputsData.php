<?php

namespace LBHurtado\Mortgage\Data\Inputs;

use LBHurtado\Mortgage\Contracts\{BuyerInterface, OrderInterface, PropertyInterface};
use LBHurtado\Mortgage\Data\Transformers\PercentToFloatTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Spatie\LaravelData\Data;

class DownPaymentInputsData extends Data
{
    public function __construct(
        #[WithTransformer(PercentToFloatTransformer::class)]
        public ?Percent $percent_dp = null,
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
