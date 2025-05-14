<?php

namespace LBHurtado\Mortgage\Data\Transformers;

use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;
use Spatie\LaravelData\Support\DataProperty;
use LBHurtado\Mortgage\ValueObjects\Percent;

class PercentToStringTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        // Check if the value is an instance of the Price class
        if ($value instanceof Percent) {
            // Return the string representation of the price object
            return (string) $value;
        }

        // Return the value unchanged if it's not of type Price
        return $value;
    }
}
