<?php

namespace App\Modifiers;

use Whitecube\Price\PriceAmendable;
use Brick\Money\AbstractMoney;
use Brick\Math\RoundingMode;
use Whitecube\Price\Vat;
use Brick\Money\Money;
use App\Classes\Buyer;

class DisposableModifier implements PriceAmendable
{
    protected string $type = 'default';
    protected Buyer $buyer;

    public function __construct(Buyer $buyer)
    {
        $this->buyer = $buyer;
    }

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
        return 'disposable';
    }

    public function attributes(): ?array
    {
        return [
            'modifier' => 'disposable income multiplier',
            'disposable_income_multiplier' => $this->buyer->getIncomeRequirementMultiplier(),

            'default_disposable_income_multiplier' => config('gnc-revelation.default_disposable_income_multiplier'),
        ];
    }

    public function appliesAfterVat(): bool
    {
        return false;
    }

    public function apply(AbstractMoney $build, float $units, bool $perUnit, ?AbstractMoney $exclusive = null, ?Vat $vat = null): ?AbstractMoney
    {
        $multiplier = $this->buyer->getIncomeRequirementMultiplier()->value();
//        $multiplier = $this->buyer->getDisposableIncomeMultiplier()->value();

        if ($build instanceof Money) {
            return $build->multipliedBy($multiplier, roundingMode: RoundingMode::CEILING);
        }

        return null;
    }
}
