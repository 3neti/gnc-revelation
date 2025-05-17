<?php

namespace LBHurtado\Mortgage\Data\Inputs;

use LBHurtado\Mortgage\Contracts\{BuyerInterface, OrderInterface, PropertyInterface};
use Spatie\LaravelData\Data;

class InputsData extends Data
{
    public function __construct(
        protected ?BuyerInterface               $buyer = null,
        protected ?PropertyInterface            $property = null,
        protected ?OrderInterface               $order = null,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static (
            buyer: $buyer,
            property: $property,
            order: $order,
        );
    }

    public function buyer(): ?BuyerInterface
    {
        return $this->buyer;
    }

    public function property(): ?PropertyInterface
    {
        return $this->property;
    }

    public function order(): ?OrderInterface
    {
        return $this->order;
    }
}
