<?php

namespace App\Modifiers;

use Brick\Math\RoundingMode;
use Brick\Money\AbstractMoney;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\PriceAmendable;
use Whitecube\Price\Vat;

/** @deprecated  */
class DownPaymentDeductionModifier implements PriceAmendable
{
    protected string $type = 'default';

    public function __construct(
        public readonly ?Percent $downPaymentPercent = null
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
        return 'down_payment_deduction';
    }

    public function attributes(): ?array
    {
        return [
            'down_payment_percent' => $this->downPaymentPercent?->value() ?? 0.0,
        ];
    }

    public function appliesAfterVat(): bool
    {
        return false;
    }

    public function apply(AbstractMoney $build, float $units, bool $perUnit, ?AbstractMoney $exclusive = null, ?Vat $vat = null): ?AbstractMoney
    {
        $dp = $this->downPaymentPercent?->value() ?? 0.0;
        $adjusted = $build->multipliedBy(1 - $dp, RoundingMode::HALF_UP);

        return $adjusted;
    }
}
