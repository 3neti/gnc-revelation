<?php

namespace LBHurtado\Mortgage\Data\Payloads;

use LBHurtado\Mortgage\Data\MortgageResultData;
use Spatie\LaravelData\Data;

class MortgageResultPayload extends Data
{
    public function __construct(
        public MortgageInputsPayload $inputs,
        public int $term_years,
        public float $monthly_disposable_income,
        public float $present_value,
        public float $required_equity,
        public float $monthly_amortization,
        public float $add_on_fees,
        public float $cash_out,
        public float $loanable_amount,
        public float $miscellaneous_fee,
    ) {}

    public static function fromResult(MortgageResultData $result): static
    {
        return new static(
            inputs: MortgageInputsPayload::fromInputs($result->inputs),
            term_years: $result->term_years,
            monthly_disposable_income: $result->monthly_disposable_income->getAmount()->toFloat(),
            present_value: $result->present_value->getAmount()->toFloat(),
            required_equity: $result->required_equity->getAmount()->toFloat(),
            monthly_amortization: $result->monthly_amortization->getAmount()->toFloat(),
            add_on_fees: $result->add_on_fees->getAmount()->toFloat(),
            cash_out: $result->cash_out->getAmount()->toFloat(),
            loanable_amount: $result->loanable_amount->getAmount()->toFloat(),
            miscellaneous_fee: $result->miscellaneous_fee->getAmount()->toFloat(),
        );
    }
}
