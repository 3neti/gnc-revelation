<?php

namespace LBHurtado\Mortgage\Traits;

use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\ValueObjects\Percent;

trait HasFinancialAttributes
{
    protected Percent $interest_rate;
    protected ?Percent $incomeRequirementMultiplier = null;
    protected ?Percent $percentMiscellaneousFees = null;
    protected ?LendingInstitution $lendingInstitution = null;

    public function setInterestRate(Percent|float|int $value): static
    {
        $this->interest_rate = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            default                         => throw new \InvalidArgumentException("Invalid value for interest rate."),
        };

        return $this;
    }

    /**
     * Returns the interest rate if set, otherwise defers to resolveDefaultInterestRate().
     *
     * NOTE: The consuming class MUST implement `resolveDefaultInterestRate()` if fallback is desired.
     * If not implemented and no interest rate is set, a LogicException will be thrown.
     */
    public function getInterestRate(): Percent
    {
        return $this->interest_rate ?? $this->resolveDefaultInterestRate();
    }

    /**
     * Should be implemented by the consuming class to provide default interest rate fallback logic.
     */
    public function resolveDefaultInterestRate(): Percent
    {
        throw new \LogicException('The class using HasFinancialAttributes must implement resolveDefaultInterestRate() or set an interest rate.');
    }

    public function getIncomeRequirementMultiplier(): ?Percent
    {
        return $this->incomeRequirementMultiplier;
    }

    public function setIncomeRequirementMultiplier(Percent|float|int|null $value): static
    {
        $this->incomeRequirementMultiplier = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            is_null($value)                 => null,
            default                         => throw new \InvalidArgumentException("Invalid value for income requirement multiplier."),
        };

        return $this;
    }

    public function getPercentMiscellaneousFees(): ?Percent
    {
        return $this->percentMiscellaneousFees;
    }

    public function setPercentMiscellaneousFees(Percent|float|int|null $value): static
    {
        $this->percentMiscellaneousFees = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            is_null($value)                 => null,
            default                         => throw new \InvalidArgumentException("Invalid value for miscellaneous fees."),
        };

        return $this;
    }

    public function getLendingInstitution(): ?LendingInstitution
    {
        return $this->lendingInstitution;
    }

    public function setLendingInstitution(LendingInstitution $institution): static
    {
        $this->lendingInstitution = $institution;
        $this->setIncomeRequirementMultiplier($this->lendingInstitution->getIncomeRequirementMultiplier());

        return $this;
    }
}
