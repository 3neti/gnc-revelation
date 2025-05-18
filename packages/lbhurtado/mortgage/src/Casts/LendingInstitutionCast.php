<?php

namespace LBHurtado\Mortgage\Casts;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Casts\Cast;
use LBHurtado\Mortgage\Classes\LendingInstitution;

class LendingInstitutionCast implements Cast
{
    public function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context
    ): mixed {
        // If the value is already a LendingInstitution instance, return it directly
        if ($value instanceof LendingInstitution) {
            return $value;
        }

        // If the value is a string, attempt to create a LendingInstitution from the key
        if (is_string($value)) {
            return new LendingInstitution($value);
        }

        // Throw an exception if the value is not a valid LendingInstitution input
        throw new \InvalidArgumentException(
            "Cannot cast value to LendingInstitution: " . print_r($value, true)
        );
    }
}
