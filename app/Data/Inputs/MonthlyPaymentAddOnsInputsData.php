<?php

namespace App\Data\Inputs;

use App\Contracts\PropertyInterface;
use App\Contracts\OrderInterface;
use App\Contracts\BuyerInterface;
use Spatie\LaravelData\Data;

class MonthlyPaymentAddOnsInputsData extends Data
{
    public function __construct(
        public float $mortgage_redemption_insurance = 0.00,
        public float $annual_fire_insurance = 0.00,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            mortgage_redemption_insurance: $order->getMortgageRedemptionInsurance(),
            annual_fire_insurance: $order->getAnnualFireInsurance()
        );
    }
}
