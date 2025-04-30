<?php

namespace App\Modifiers;

use Brick\Math\RoundingMode;
use Brick\Money\AbstractMoney;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\PriceAmendable;
use Whitecube\Price\Vat;

/** @deprecated  */
class MiscellaneousFeeModifier implements PriceAmendable
{
    protected string $type = 'default';

    public function __construct(
        public readonly ?Percent $mfPercent = null,
        public readonly ?Percent $dpPercent = null // not used yet
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
        return 'balance_miscellaneous_fee';
    }

    public function attributes(): ?array
    {
        return [
            'percent_misc_fee' => $this->mfPercent?->value() ?? 0.0,
            'percent_down_payment' => $this->dpPercent?->value() ?? 0.0,
        ];
    }

    public function appliesAfterVat(): bool
    {
        return false;
    }

    public function apply(AbstractMoney $build, float $units, bool $perUnit, ?AbstractMoney $exclusive = null, ?Vat $vat = null): ?AbstractMoney
    {
        $mf = $this->mfPercent?->value() ?? 0.0;

        return $build->multipliedBy(1 + $mf, RoundingMode::HALF_UP);
    }
}
