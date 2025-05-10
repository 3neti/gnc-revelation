<?php

namespace LBHurtado\Mortgage\Attributes;

use LBHurtado\Mortgage\Enums\ExtractorType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ExtractorFor
{
    public function __construct(public ExtractorType $type) {}
}
