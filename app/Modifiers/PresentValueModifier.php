<?php

namespace App\Modifiers;

use Brick\Math\RoundingMode;
use Brick\Money\AbstractMoney;
use Brick\Money\Money;
use Whitecube\Price\PriceAmendable;
use Whitecube\Price\Vat;

class PresentValueModifier implements PriceAmendable
{
    protected string $type = 'default';

    public function __construct(
        public readonly int $bpTermYears,
        public readonly float $bpInterestRateAnnual // e.g. 0.0625 for 6.25%
    ) {}

    public function type(): string
    {
        return $this->type;
    }

    public function setType(?string $type = null): static
    {
        $this->type = $type ?? 'default';
        return $this;
    }

    public function key(): ?string
    {
        return 'present_value';
    }

    public function attributes(): ?array
    {
        return [
            'bp_term_months' => $this->bpTermYears * 12,
            'monthly_interest_rate' => $this->getMonthlyRate(),
        ];
    }

    public function appliesAfterVat(): bool
    {
        return false;
    }

    public function apply(AbstractMoney $build, float $units, bool $perUnit, ?AbstractMoney $exclusive = null, ?Vat $vat = null): ?AbstractMoney
    {
        $months = $this->bpTermYears * 12;
        $rate = $this->getMonthlyRate();
        $amount = $build->getAmount()->toFloat();

        $presentValue = abs($rate) < 1e-10
            ? $amount * $months
            : $amount * (1 - pow(1 + $rate, -$months)) / $rate;

        return Money::of($presentValue, $build->getCurrency(), roundingMode: RoundingMode::HALF_UP);
    }

    protected function getMonthlyRate(): float
    {
        return round($this->bpInterestRateAnnual / 12, 15);
    }
}
