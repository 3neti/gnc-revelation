<?php

namespace Tests\Fakes;

use App\Contracts\PropertyInterface;
use App\ValueObjects\Percent;
use Whitecube\Price\Price;
use Brick\Money\Money;

/** @deprecated  */
class FakeProperty implements PropertyInterface
{
    public function getLoanableAmount(): Price
    {
        return new Price(Money::of(1000000, 'PHP')); // ₱1M loan
    }

    public function getInterestRate(): Percent
    {
        return Percent::ofFraction(0.06); // 6% annual interest
    }

    public function getRequiredBufferMargin(): ?Percent
    {
        return Percent::ofFraction(0.1);
    }

    public function getTotalContractPrice(): Price
    {
        // TODO: Implement getTotalContractPrice() method.
    }

    public function getIncomeRequirementMultiplier(): ?float
    {
        // TODO: Implement getIncomeRequirementMultiplier() method.
    }

    public function getPercentLoanableValue(): ?Percent
    {
        // TODO: Implement getPercentLoanable() method.
    }

    public function getAppraisalValue(): ?Price
    {
        // TODO: Implement getAppraisalValue() method.
    }

    public function getProcessingFee(): ?Price
    {
        // TODO: Implement getProcessingFee() method.
    }

    public function getPercentMiscellaneousFees(): ?Percent
    {
        // TODO: Implement getPercentMiscellaneousFees() method.
    }

    public function getPercentDisposableIncomeRequirement(): Percent
    {
        // TODO: Implement getPercentDisposableIncomeRequirement() method.
    }

    public function resolveDefaultInterestRate(): Percent
    {
        // TODO: Implement resolveDefaultInterestRate() method.
    }
}
