<?php

namespace LBHurtado\Mortgage\Extractors;

use LBHurtado\Mortgage\Contracts\ExtractorInterface;
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;

abstract class BaseExtractor implements ExtractorInterface
{
    public function __construct(public MortgageParticulars $inputs) {}

    public static function fromInputs(MortgageParticulars $inputs): static
    {
        return new static($inputs);
    }

    abstract public function extract(): mixed;
}
