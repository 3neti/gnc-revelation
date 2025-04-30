<?php

namespace LBHurtado\Mortgage\Modifiers;

use Brick\Money\{AbstractMoney, Money};
use Whitecube\Price\PriceAmendable;
use Brick\Math\RoundingMode;
use Whitecube\Price\Vat;

class PeriodicPaymentModifier implements PriceAmendable
{
    protected string $type = 'default';

    public function __construct(
        public readonly int $termInMonths,
        public readonly float $monthlyRate // e.g., 0.00520833 for 6.25% annually
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
        return 'periodic_payment';
    }

    public function attributes(): ?array
    {
        return [
            'term_months' => $this->termInMonths,
            'monthly_rate' => $this->monthlyRate,
        ];
    }

    public function appliesAfterVat(): bool
    {
        return false;
    }

    public function apply(AbstractMoney $build, float $units, bool $perUnit, ?AbstractMoney $exclusive = null, ?Vat $vat = null): ?AbstractMoney
    {
        $amount = $build->getAmount()->toFloat();

        $payment = abs($this->monthlyRate) < 1e-10
            ? $amount / $this->termInMonths
            : ($amount * $this->monthlyRate) / (1 - pow(1 + $this->monthlyRate, -$this->termInMonths));

        return Money::of($payment, $build->getCurrency(), roundingMode: RoundingMode::HALF_UP);
    }
}
