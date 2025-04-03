<?php

namespace App\DataObjects;

readonly class MortgageTerm
{
    public function __construct(
        public int    $value,
        public string $cycle = 'yearly', // or 'monthly'
    ) {}

    public function months(): int
    {
        return $this->cycle === 'monthly' ? $this->value : $this->value * 12;
    }

    public function years(): float
    {
        return $this->cycle === 'monthly' ? round($this->value / 12, 1) : $this->value;
    }

    public function monthsToPay(): int
    {
        return $this->months();
    }

    public function yearsToPay(): float
    {
        return $this->years();
    }
}
