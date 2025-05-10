<?php

namespace Tests\Fakes;

use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

/** @deprecated  */
class FlexibleFakeProperty implements PropertyInterface
{
    public function __construct(
        protected float  $total_contract_price,
        protected ?float $interest = 0.625,
        protected ?float $income_requirement_multiplier = 0.35,
        protected ?float $loanable_amount = null,
        protected ?float $bufferMargin = 0.10,
    ) {}

    public function getTotalContractPrice(): Price
    {
        return MoneyFactory::priceWithPrecision($this->total_contract_price);
    }

    public function getLoanableAmount(): Price
    {
        return MoneyFactory::priceWithPrecision($this->loanable_amount);
    }

    public function getInterestRate(): Percent
    {
        return Percent::ofFraction($this->interest);
    }

//    public function getRequiredBufferMargin(): ?Percent
//    {
//        return Percent::ofFraction($this->bufferMargin);
//    }


    public function getRequiredBufferMargin(): ?Percent
    {
        return isset($this->bufferMargin)
            ? Percent::ofFraction($this->bufferMargin)
            : null;
    }

    public function getIncomeRequirementMultiplier(): ?float
    {
        return $this->income_requirement_multiplier;
    }

    public function getPercentLoanableValue(): ?Percent { return null; }
    public function getAppraisalValue(): ?Price { return null; }
    public function getProcessingFee(): ?Price { return null; }
    public function getPercentMiscellaneousFees(): Percent { return Percent::ofFraction(0); }

    public function getIncomeRequirementMultiplier(): Percent
    {
        // TODO: Implement getPercentDisposableIncomeRequirement() method.
    }

    public function resolveDefaultInterestRate(): Percent
    {
        // TODO: Implement resolveDefaultInterestRate() method.
    }
}
