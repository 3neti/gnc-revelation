<?php

namespace LBHurtado\Mortgage\Data\Payloads;

use LBHurtado\Mortgage\Data\Inputs\InputsData;
use Spatie\LaravelData\Data;

class MortgageInputsPayload extends Data
{
    public function __construct(
        public float $gross_monthly_income,
        public ?float $income_requirement_multiplier,

        public float $total_contract_price,
        public ?float $percent_down_payment,
//        public ?int $down_payment_term,
        public ?float $percent_loanable,
        public ?float $appraisal_value,
        public ?float $discount_amount,
        public ?float $low_cash_out,
        public ?float $waived_processing_fee,

        public int $balance_payment_term,
        public float $balance_payment_interest_rate,

        public ?float $percent_miscellaneous_fee,
        public ?float $consulting_fee,
        public ?float $processing_fee,

//        public float $monthly_mri,
//        public float $monthly_fi,
    ) {}

    public static function fromInputs(InputsData $inputs): static
    {
        return new static(
            gross_monthly_income: $inputs->income->gross_monthly_income->inclusive()->getAmount()->toFloat(),
            income_requirement_multiplier: $inputs->income->income_requirement_multiplier?->value(),

            total_contract_price: $inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat(),
            percent_down_payment: $inputs->loanable->down_payment->percent_dp?->value(),
//            down_payment_term: $inputs->loanable->down_payment->dp_term,
            percent_loanable: $inputs->loanable->percent_loanable?->value(),
            appraisal_value: $inputs->loanable->appraisal_value?->inclusive()->getAmount()->toFloat(),
            discount_amount: $inputs->loanable->discount_amount?->inclusive()->getAmount()->toFloat(),
            low_cash_out: $inputs->loanable->low_cash_out,
            waived_processing_fee: $inputs->loanable->waived_processing_fee,

            balance_payment_term: $inputs->balance_payment->bp_term,
            balance_payment_interest_rate: $inputs->balance_payment->bp_interest_rate->value(),

            percent_miscellaneous_fee: $inputs->fees?->percent_mf?->value(),
            consulting_fee: $inputs->fees?->consulting_fee?->inclusive()->getAmount()->toFloat(),
            processing_fee: $inputs->fees?->processing_fee?->inclusive()->getAmount()->toFloat(),

//            monthly_mri: $inputs->monthly_payment_add_ons?->monthly_mri ?? 0.0,
//            monthly_fi: $inputs->monthly_payment_add_ons?->monthly_fi ?? 0.0,
        );
    }
}
