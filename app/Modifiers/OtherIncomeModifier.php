<?php

namespace App\Modifiers;

use Whitecube\Price\PriceAmendable;
use Brick\Money\AbstractMoney;
use Brick\Money\Money;
use Brick\Math\RoundingMode;
use Whitecube\Price\Vat;

class OtherIncomeModifier implements PriceAmendable
{
    protected Money $amount;
    protected string $type = 'fixed';

    public function __construct(Money $amount)
    {
        $this->amount = $amount;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function setType(?string $type = null): static
    {
        $this->type = $type ?? 'fixed';
        return $this;
    }

    public function key(): ?string
    {
        return 'other_income_' . $this->amount->getAmount();
    }

    public function attributes(): ?array
    {
        return [
            'source' => 'other income',
            'value' => $this->amount->getAmount()->toFloat(),
        ];
    }

    public function appliesAfterVat(): bool
    {
        return false;
    }

    public function apply(AbstractMoney $build, float $units, bool $perUnit, ?AbstractMoney $exclusive = null, ?Vat $vat = null): ?AbstractMoney
    {
        if ($build instanceof Money) {
            return $build->plus($this->amount, RoundingMode::CEILING);
        }

        return null;
    }
}
