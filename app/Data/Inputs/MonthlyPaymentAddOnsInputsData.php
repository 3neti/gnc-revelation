<?php

namespace App\Data\Inputs;

use App\Contracts\PropertyInterface;
use App\Contracts\OrderInterface;
use App\Contracts\BuyerInterface;
use App\Enums\Order\MonthlyFee;
use Spatie\LaravelData\Data;

class MonthlyPaymentAddOnsInputsData extends Data
{
    public function __construct(
        public float $monthly_mri = 0.00,
        public float $monthly_fi = 0.00,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            monthly_mri: $order->getMonthlyFee(MonthlyFee::MRI) ?? 0.0,
            monthly_fi: $order->getMonthlyFee(MonthlyFee::FIRE_INSURANCE) ?? 0.0,
        );
    }
}
