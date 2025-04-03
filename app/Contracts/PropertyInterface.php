<?php

namespace App\Contracts;

use Whitecube\Price\Price;

interface PropertyInterface
{
    public function getLoanableAmount(): Price;
    public function getInterestRate(): float;
    public function getRequiredBufferMargin(): ?float;
}
