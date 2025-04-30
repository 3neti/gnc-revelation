<?php

namespace LBHurtado\Mortgage\Data\Inputs;

use LBHurtado\Mortgage\Contracts\{BuyerInterface, OrderInterface, PropertyInterface};
use LBHurtado\Mortgage\ValueObjects\Percent;
use Spatie\LaravelData\Data;

class FeesInputsData extends Data
{
    public function __construct(
        public ?Percent $percent_mf = null,
        public ?float $consulting_fee = null,
        public ?float $processing_fee = null,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            percent_mf: $order->getPercentMiscellaneousFees() ?? $property->getPercentMiscellaneousFees(),
            consulting_fee: $order->getConsultingFee()?->inclusive()->getAmount()->toFloat(),
            processing_fee: $order->getProcessingFee()?->inclusive()->getAmount()->toFloat() ?? $property->getProcessingFee()?->inclusive()->getAmount()->toFloat(),
        );
    }
}
