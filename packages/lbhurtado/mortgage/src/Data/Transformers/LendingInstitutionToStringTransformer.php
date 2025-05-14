<?php

namespace LBHurtado\Mortgage\Data\Transformers;

use Spatie\LaravelData\Support\Transformation\TransformationContext;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use Spatie\LaravelData\Transformers\Transformer;
use Spatie\LaravelData\Support\DataProperty;

class LendingInstitutionToStringTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        // Check if the value is an instance of the Price class
        if ($value instanceof LendingInstitution) {
            // Return the string representation of the LendingInstitution object
            return $value->key();
        }

        // Return the value unchanged if it's not of type Price
        return $value;
    }

}
