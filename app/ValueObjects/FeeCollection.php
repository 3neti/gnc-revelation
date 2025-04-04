<?php

namespace App\ValueObjects;

use Illuminate\Support\Collection;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class FeeCollection
{
    protected Collection $addOns;
    protected Collection $deductibles;
    protected string $currency;

    public function __construct(array $addOns = [], array $deductibles = [], string $currency = 'PHP')
    {
        $this->currency = $currency;
        $this->addOns = collect($addOns)->map(fn ($value) => Money::of($value, $currency));
        $this->deductibles = collect($deductibles)->map(fn ($value) => Money::of($value, $currency));
    }

    public function addAddOn(string $label, float $amount): static
    {
        $this->addOns->put($label, Money::of($amount, $this->currency));
        return $this;
    }

    public function addDeductible(string $label, float $amount): static
    {
        $this->deductibles->put($label, Money::of($amount, $this->currency));
        return $this;
    }

    public function totalAddOns(): Money
    {
        return $this->addOns->reduce(
            fn (Money $carry, Money $item) => $carry->plus($item),
            Money::of(0, $this->currency)
        );
    }

    public function totalDeductibles(): Money
    {
        return $this->deductibles->reduce(
            fn (Money $carry, Money $item) => $carry->plus($item),
            Money::of(0, $this->currency)
        );
    }

    public function netFees(): Money
    {
        return $this->totalAddOns()->minus($this->totalDeductibles());
    }

    public function allAddOns(): Collection
    {
        return $this->addOns;
    }

    public function allDeductibles(): Collection
    {
        return $this->deductibles;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}
