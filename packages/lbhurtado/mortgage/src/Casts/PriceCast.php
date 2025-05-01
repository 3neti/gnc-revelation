<?php

namespace LBHurtado\Mortgage\Casts;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Casts\Cast;
use Whitecube\Price\Price;

class PriceCast implements Cast
{
    public function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context
    ): mixed {
        if ($value instanceof Price) {
            return $value;
        }

        if (is_array($value) && isset($value['amount'], $value['currency'])) {
            return Price::of($value['amount'], $value['currency']);
        }

        if (is_numeric($value)) {
            return Price::of($value, 'PHP'); // default currency
        }

        throw new \InvalidArgumentException("Cannot cast value to Price: " . print_r($value, true));
    }
}
