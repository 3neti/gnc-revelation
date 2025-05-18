<?php

namespace LBHurtado\Mortgage\Data\Inputs;

use LBHurtado\Mortgage\Contracts\{BuyerInterface, OrderInterface, PropertyInterface};

class MortgageParticulars
{
    public function __construct(
        public BuyerInterface $buyer,
        public PropertyInterface $property,
        public OrderInterface $order,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static (
            buyer: $buyer,
            property: $property,
            order: $order,
        );
    }

    public function buyer(): BuyerInterface
    {
        return $this->buyer;
    }

    public function property(): PropertyInterface
    {
        return $this->property;
    }

    public function order(): OrderInterface
    {
        return $this->order;
    }
}
