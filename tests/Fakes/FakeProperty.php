<?php

namespace Tests\Fakes;

use App\Contracts\PropertyInterface;
use Whitecube\Price\Price;
use Brick\Money\Money;

class FakeProperty implements PropertyInterface
{
    public function getLoanableAmount(): Price
    {
        return new Price(Money::of(1000000, 'PHP')); // ₱1M loan
    }

    public function getInterestRate(): float
    {
        return 0.06; // 6% annual interest
    }

    public function getRequiredBufferMargin(): ?float
    {
        return 0.1;
    }

    public function getTotalContractPrice(): Price
    {
        // TODO: Implement getTotalContractPrice() method.
    }

    public function getIncomeRequirementMultiplier(): ?float
    {
        // TODO: Implement getIncomeRequirementMultiplier() method.
    }

    public function getPercentLoanable(): ?float
    {
        // TODO: Implement getPercentLoanable() method.
    }

    public function getAppraisalValue(): ?float
    {
        // TODO: Implement getAppraisalValue() method.
    }

    public function getProcessingFee(): ?Price
    {
        // TODO: Implement getProcessingFee() method.
    }

    public function getPercentMiscellaneousFees(): ?float
    {
        // TODO: Implement getPercentMiscellaneousFees() method.
    }
}
