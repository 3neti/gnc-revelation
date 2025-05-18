<?php

namespace LBHurtado\Mortgage\Casts;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Casts\Cast;
use LBHurtado\Mortgage\ValueObjects\Percent;

class PercentCast implements Cast
{
    public function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context
    ): mixed {
        if ($value instanceof Percent) {
            return $value;
        }

        if (is_numeric($value)) {
            if ($value <= 1) {
                // Treat as a fraction when <= 1
                return Percent::ofFraction($value);
            }

            // Treat as a percentage otherwise
            return Percent::ofPercent($value);
        }

        throw new \InvalidArgumentException("Cannot cast value to Percent: " . print_r($value, true));
    }
}
