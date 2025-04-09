<?php

namespace App\Contracts;

use App\ValueObjects\Percent;
use Whitecube\Price\Price;

interface BuyerInterface
{
    public function getMonthlyGrossIncome(): Price;
    public function getJointMonthlyDisposableIncome(): Price;
    public function getJointMaximumTermAllowed(): int;
    public function getInterestRate(): ?Percent;
    public function getDownPaymentTerm(): ?int;
    public function getBalancePaymentTerm(): ?int;
}
