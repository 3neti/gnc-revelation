<?php

namespace LBHurtado\Mortgage\Contracts;

use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

interface PropertyInterface
{
    public function getRequiredBufferMargin(): ?Percent;
    public function getTotalContractPrice(): Price;
    public function getIncomeRequirementMultiplier(): ?Percent;
    public function getPercentLoanableValue(): ?Percent;
    public function getLoanableAmount(): Price;
    public function getAppraisalValue(): ?Price;
    public function getProcessingFee(): ?Price;
    public function getPercentMiscellaneousFees(): ?Percent;
    public function getLendingInstitution(): ?LendingInstitution;
    public function getPercentDownPayment(): Percent;

    /**
     * Must return an interest rate, either explicitly set or via fallback logic.
     */
    public function getInterestRate(): Percent;

    /**
     * Must provide default interest rate fallback logic for use by HasFinancialAttributes.
     */
    public function resolveDefaultInterestRate(): Percent;
}
