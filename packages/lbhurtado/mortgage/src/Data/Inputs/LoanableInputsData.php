<?php

namespace LBHurtado\Mortgage\Data\Inputs;

use LBHurtado\Mortgage\Contracts\{BuyerInterface, OrderInterface, PropertyInterface};
use LBHurtado\Mortgage\Data\Transformers\PercentToFloatTransformer;
use LBHurtado\Mortgage\Data\Transformers\PriceToFloatTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class LoanableInputsData extends Data
{
    public function __construct(
        #[WithTransformer(PriceToFloatTransformer::class)]
        public Price $total_contract_price,
        public DownPaymentInputsData $down_payment,
        #[WithTransformer(PercentToFloatTransformer::class)]
        public ?Percent $percent_loanable = null,
        #[WithTransformer(PriceToFloatTransformer::class)]
        public ?Price $appraisal_value = null,
        #[WithTransformer(PriceToFloatTransformer::class)]
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
