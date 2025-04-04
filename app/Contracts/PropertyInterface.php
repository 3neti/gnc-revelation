<?php

namespace App\Contracts;

use Whitecube\Price\Price;

interface PropertyInterface
{
    public function getTotalContractPrice(): Price;
    public function getLoanableAmount(): Price;
    public function getInterestRate(): ?float;
    public function getRequiredBufferMargin(): ?float;
    public function getIncomeRequirementMultiplier(): ?float;
    public function getPercentLoanable(): ?float;
    public function getAppraisalValue(): ?float;
    public function getProcessingFee(): ?Price;
    public function getPercentMiscellaneousFees(): ?float;
}
