<?php

namespace LBHurtado\Mortgage\Data\Models;

use LBHurtado\Mortgage\Data\Transformers\LendingInstitutionToStringTransformer;
use LBHurtado\Mortgage\Data\Transformers\PercentToFloatTransformer;
use LBHurtado\Mortgage\Data\Transformers\PriceToFloatTransformer;
use LBHurtado\Mortgage\Enums\Property\DevelopmentForm;
use LBHurtado\Mortgage\Enums\Property\DevelopmentType;
use Spatie\LaravelData\Transformers\EnumTransformer;
use LBHurtado\Mortgage\Enums\Property\HousingType;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use Spatie\LaravelData\Attributes\WithTransformer;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Models\Property;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class PropertyData extends Data
{
    public function __construct(
        public string $code,
        public string $name,
        public string|null $type, //new
        public string|null $cluster, //new
        public string|null $status,
        public string|null $sku,
        public string|null $project_code, //new
        #[WithTransformer(PriceToFloatTransformer::class)]
        public Price $total_contract_price,
        #[WithTransformer(PriceToFloatTransformer::class)]
        public Price $appraisal_value,
        #[WithTransformer(EnumTransformer::class)]
        public ?DevelopmentType $development_type,
        #[WithTransformer(EnumTransformer::class)]
        public ?DevelopmentForm $development_form,
        #[WithTransformer(EnumTransformer::class)]
        public ?HousingType $housing_type,
        #[WithTransformer(PercentToFloatTransformer::class)]
        public Percent|null $percent_loanable_value,
        #[WithTransformer(PercentToFloatTransformer::class)]
        public Percent|null $percent_miscellaneous_fees,
        #[WithTransformer(PriceToFloatTransformer::class)]
        public Price $processing_fee,
        #[WithTransformer(PercentToFloatTransformer::class)]
        public ?Percent $required_buffer_margin,
        #[WithTransformer(LendingInstitutionToStringTransformer::class)]
        public ?LendingInstitution $lending_institution,
        #[WithTransformer(PercentToFloatTransformer::class)]
        public Percent $income_requirement_multiplier,
        #[WithTransformer(PercentToFloatTransformer::class)]
        public Percent $percent_down_payment,
    ) {}

    public static function fromModel(Property $property): self
    {
        return new self(
            code: $property->code,
            name: $property->name,
            type: $property->type, //new
            cluster: $property->cluster, //new
            status: $property->status,
            sku: $property->sku,
            project_code: $property->project_code,
            total_contract_price: $property->total_contract_price,
            appraisal_value: $property->appraisal_value,
            development_type: $property->development_type,
            development_form: $property->development_form,
            housing_type: $property->housing_type,
            percent_loanable_value: $property->percent_loanable_value,
            percent_miscellaneous_fees: $property->percent_miscellaneous_fees,
            processing_fee: $property->processing_fee,
            required_buffer_margin: $property->required_buffer_margin,
            lending_institution: $property->lending_institution, // Expecting a LendingInstitution object
            income_requirement_multiplier: $property->income_requirement_multiplier, // Extract numeric value
            percent_down_payment: $property->percent_down_payment,
        );
    }
}
