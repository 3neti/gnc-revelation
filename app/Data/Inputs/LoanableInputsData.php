<?php

namespace App\Data\Inputs;

use App\Contracts\PropertyInterface;
use App\Contracts\OrderInterface;
use App\Contracts\BuyerInterface;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class LoanableInputsData extends Data
{
    public function __construct(
        public Price $total_contract_price,
        public DownPaymentInputsData $down_payment,
        public ?float $percent_loanable = 1.00,
        public ?Price $appraisal_value = null,
        public ?Price $discount_amount  = null,
        public ?float $low_cash_out = null,
        public ?float $waived_processing_fee  = null
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static(
            total_contract_price: $property->getTotalContractPrice(),
            down_payment: DownPaymentInputsData::fromBooking($buyer, $property, $order),
            percent_loanable: $property->getPercentLoanable(),
            appraisal_value: $property->getTotalContractPrice(),
            discount_amount: $order->getDiscountAmount(),
            low_cash_out: $order->getLowCashOut(),
            waived_processing_fee: $order->getWaivedProcessingFee()
        );
    }
}
