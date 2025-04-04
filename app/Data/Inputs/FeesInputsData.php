<?php

namespace App\Data\Inputs;

use App\Contracts\BuyerInterface;
use App\Contracts\OrderInterface;
use App\Contracts\PropertyInterface;
use Spatie\LaravelData\Data;

class FeesInputsData extends Data
{
    public function __construct(
        public ?float $percent_mf = null,
        public ?float $consulting_fee = null,
        public ?float $processing_fee = null,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            percent_mf: $order->getPercentMiscellaneousFees() ?? $property->getPercentMiscellaneousFees(),
            consulting_fee: $order->getConsultingFee(),
            processing_fee: $order->getProcessingFee() ?? $property->getProcessingFee(),
        );
    }
}
