<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Contracts\CalculatorInterface;
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;

abstract class BaseCalculator implements CalculatorInterface
{
    public function __construct(public MortgageParticulars $inputs) {}

    public static function fromInputs(MortgageParticulars $inputs): static
    {
        return new static($inputs);
    }

    abstract public function calculate(): mixed;
}
