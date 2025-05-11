<?php

namespace LBHurtado\Mortgage\Data\Models;

use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Models\Property;
use Spatie\LaravelData\Data;

class PropertyData extends Data
{
    public function __construct(
        public string $code,
        public string $name,
        public string $status,
        public float $total_contract_price,
        public float $appraisal_value,
        public float $percent_loanable_value,
        public float $percent_miscellaneous_fees,
        public float $processing_fee,
        public ?LendingInstitution $lending_institution,
        public float $income_requirement_multiplier,

    ) {}

    public static function fromModel(Property $property): self
    {
        return new self(
            code: $property->code,
            name: $property->name,
            status: $property->status,
            total_contract_price: $property->total_contract_price?->inclusive()->getAmount()->toFloat() ?? 0.0,
            appraisal_value: $property->appraisal_value?->inclusive()->getAmount()->toFloat() ?? 0.0,
            percent_loanable_value: $property->percent_loanable_value?->value() ?? 0.0,
            percent_miscellaneous_fees: $property->percent_miscellaneous_fees?->value() ?? 0.0,
            processing_fee: $property->processing_fee?->inclusive()->getAmount()->toFloat() ?? 0.0,
            lending_institution: $property->lending_institution, // Expecting a LendingInstitution object
            income_requirement_multiplier: $property->income_requirement_multiplier?->value() ?? 0.0, // Extract numeric value

        );
    }
}
