<?php

namespace LBHurtado\Mortgage\Data\Inputs;

use LBHurtado\Mortgage\Contracts\{BuyerInterface, OrderInterface, PropertyInterface};
use Spatie\LaravelData\Data;

class InputsData extends Data
{
    public function __construct(
//        public IncomeInputsData                 $income,
//        public LoanableInputsData               $loanable,
//        public BalancePaymentInputsData         $balance_payment,
//        public ?FeesInputsData                  $fees = null,
//        public ?MonthlyPaymentAddOnsInputsData  $monthly_payment_add_ons = null,
        protected ?BuyerInterface               $buyer = null,
        protected ?PropertyInterface            $property = null,
        protected ?OrderInterface               $order = null,
    ) {}

    public static function fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): static
    {
        return new static (
//            income: IncomeInputsData::fromBooking($buyer, $property, $order),
//            loanable: LoanableInputsData::fromBooking($buyer, $property, $order),
//            balance_payment: BalancePaymentInputsData::fromBooking($buyer, $property, $order),
//            fees: FeesInputsData::fromBooking($buyer, $property, $order),
//            monthly_payment_add_ons: MonthlyPaymentAddOnsInputsData::fromBooking($buyer, $property, $order),
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
