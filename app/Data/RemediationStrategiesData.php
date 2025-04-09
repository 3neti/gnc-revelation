<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RemediationStrategiesData extends Data
{
    public function __construct(
        public bool $add_income_sources,
        public bool $add_coborrower,
        public bool $switch_to_cheaper_property,
    ) {}
}
