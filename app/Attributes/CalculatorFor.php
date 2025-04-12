<?php

namespace App\Attributes;

use Attribute;
use App\Enums\CalculatorType;

#[Attribute(Attribute::TARGET_CLASS)]
class CalculatorFor
{
    public function __construct(public CalculatorType $type) {}
}
