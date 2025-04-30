<?php

namespace LBHurtado\Mortgage\Data\Inputs;

use LBHurtado\Mortgage\Contracts\{BuyerInterface, OrderInterface, PropertyInterface};
use LBHurtado\Mortgage\ValueObjects\Percent;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class LoanableInputsData extends Data
{
    public function __construct(
        public Price $total_contract_price,
        public DownPaymentInputsData $down_payment,
        public ?Percent $percent_loanable = null,
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
            percent_loanable: $property->getPercentLoanableValue(),
            appraisal_value: $property->getTotalContractPrice(),
            discount_amount: $order->getDiscountAmount(),
            low_cash_out: $order->getLowCashOut(),
            waived_processing_fee: $order->getWaivedProcessingFee()
        );
    }
}
