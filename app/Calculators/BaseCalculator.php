<?php

namespace App\Calculators;

use App\Data\Inputs\InputsData;

abstract class BaseCalculator implements CalculatorInterface
{
    public function __construct(public InputsData $inputs) {}

    public static function fromInputs(InputsData $inputs): static
    {
        return new static($inputs);
    }

    abstract public function calculate(): mixed;
}
