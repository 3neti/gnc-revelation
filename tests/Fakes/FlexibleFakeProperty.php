<?php

namespace Tests\Fakes;

use App\Contracts\PropertyInterface;
use App\Support\MoneyFactory;
use Whitecube\Price\Price;

class FlexibleFakeProperty implements PropertyInterface
{
    public function __construct(
        protected float  $total_contract_price,
        protected ?float $interest = null,
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

    public function getInterestRate(): ?float
    {
        return $this->interest;
    }

    public function getRequiredBufferMargin(): ?float
    {
        return $this->bufferMargin;
    }

    public function getIncomeRequirementMultiplier(): ?float
    {
        return $this->income_requirement_multiplier;
    }

    public function getPercentLoanable(): ?float { return null; }
    public function getAppraisalValue(): ?float { return null; }
    public function getProcessingFee(): ?Price { return null; }
    public function getPercentMiscellaneousFees(): ?float { return null; }
}
