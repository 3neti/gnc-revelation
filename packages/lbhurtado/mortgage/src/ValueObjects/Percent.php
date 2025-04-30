<?php

namespace LBHurtado\Mortgage\ValueObjects;

final class Percent
{
    private float $value; // Always normalized (e.g., 0.10 for 10%)

    private function __construct(float $normalized)
    {
        $this->value = $normalized;
    }

    public static function ofFraction(float $value): self
    {
        if ($value < 0 || $value > 1) {
            throw new \InvalidArgumentException("Fraction must be between 0 and 1.");
        }

        return new self($value);
    }

    public static function ofPercent(float $percent): self
    {
        if ($percent < 0 || $percent > 100) {
            throw new \InvalidArgumentException("Percent must be between 0 and 100.");
        }

        return new self($percent / 100);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function asPercent(): float
    {
        return $this->value * 100;
    }

    public function multiply(float $multiplier): float
    {
        return $this->value * $multiplier;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        return new self($this->value - $other->value);
    }

    public function equals(self $other, float $precision = 1e-6): bool
    {
        return abs($this->value - $other->value) <= $precision;
    }

//    public function equals(self $other): bool
//    {
//        return abs($this->value - $other->value) < 0.00001;
//    }

    public function greaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function lessThan(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function __toString(): string
    {
        return number_format($this->asPercent(), 2) . '%';
    }
}
