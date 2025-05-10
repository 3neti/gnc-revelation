<?php

namespace LBHurtado\Mortgage\Contracts;

use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

interface BuyerInterface
{
    public function getMonthlyGrossIncome(): Price;
    public function getJointMonthlyDisposableIncome(): Price;
    public function getJointMaximumTermAllowed(): int;
    public function getInterestRate(): ?Percent;
    public function getDownPaymentTerm(): ?int;
    public function getBalancePaymentTerm(): ?int;
    public function getLendingInstitution(): ?LendingInstitution;
}
