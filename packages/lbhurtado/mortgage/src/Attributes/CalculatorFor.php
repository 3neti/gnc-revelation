<?php

namespace LBHurtado\Mortgage\Attributes;

use LBHurtado\Mortgage\Enums\CalculatorType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class CalculatorFor
{
    public function __construct(public CalculatorType $type) {}
}
