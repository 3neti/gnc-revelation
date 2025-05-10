<?php

namespace LBHurtado\Mortgage\Modifiers;

use LBHurtado\Mortgage\Exceptions\IncomeRequirementMultiplierNotSetException;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Brick\Money\{AbstractMoney, Money};
use LBHurtado\Mortgage\Classes\Buyer;
use Whitecube\Price\PriceAmendable;
use Brick\Math\RoundingMode;
use Whitecube\Price\Vat;

class DisposableModifier implements PriceAmendable
{
    protected string $type = 'default';
    protected Buyer $buyer;
    protected Percent $incomeRequirementMultiplier;

    public function __construct(Percent $incomeRequirementMultiplier)
    {
//        $this->buyer = $buyer;
//        $this->incomeRequirementMultiplier = $buyer->getIncomeRequirementMultiplier();
        $this->incomeRequirementMultiplier = $incomeRequirementMultiplier;
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
            'disposable_income_multiplier' => $this->incomeRequirementMultiplier->asPercent(),
            'default_disposable_income_multiplier' => config('gnc-revelation.default_disposable_income_multiplier'),
        ];
    }

    public function appliesAfterVat(): bool
    {
        return false;
    }

    public function apply(AbstractMoney $build, float $units, bool $perUnit, ?AbstractMoney $exclusive = null, ?Vat $vat = null): ?AbstractMoney
    {
        $multiplier = $this->incomeRequirementMultiplier?->value();
        if ($multiplier === null) {
            throw new IncomeRequirementMultiplierNotSetException();
        }

        if ($build instanceof Money) {
            return $build->multipliedBy($multiplier, roundingMode: RoundingMode::CEILING);
        }

        return null;
    }
}
