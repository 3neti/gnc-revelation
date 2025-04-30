<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Contracts\CalculatorInterface;
use LBHurtado\Mortgage\Data\Inputs\InputsData;

abstract class BaseCalculator implements CalculatorInterface
{
    public function __construct(public InputsData $inputs) {}

    public static function fromInputs(InputsData $inputs): static
    {
        return new static($inputs);
    }

    abstract public function calculate(): mixed;
}
