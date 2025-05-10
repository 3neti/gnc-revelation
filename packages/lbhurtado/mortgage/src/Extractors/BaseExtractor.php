<?php

namespace LBHurtado\Mortgage\Extractors;

use LBHurtado\Mortgage\Contracts\ExtractorInterface;
use LBHurtado\Mortgage\Data\Inputs\InputsData;

abstract class BaseExtractor implements ExtractorInterface
{
    public function __construct(public InputsData $inputs) {}

    public static function fromInputs(InputsData $inputs): static
    {
        return new static($inputs);
    }

    abstract public function extract(): mixed;
}
