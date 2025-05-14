<?php

namespace LBHurtado\Mortgage\Data\Transformers;

use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;
use Spatie\LaravelData\Support\DataProperty;
use Whitecube\Price\Price;

class PriceToStringTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        // Check if the value is an instance of the Price class
        if ($value instanceof Price) {
            // Return the string representation of the price object
            return Price::format($value, 'en_PH');
//            return (string) $value;
        }

        // Return the value unchanged if it's not of type Price
        return $value;
    }

}
