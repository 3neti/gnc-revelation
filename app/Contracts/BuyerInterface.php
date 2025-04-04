<?php

namespace App\Contracts;

use Illuminate\Support\Collection;
use Whitecube\Price\Price;
use Carbon\Carbon;

interface BuyerInterface
{
    public function getGrossMonthlyIncome(): Price;
    public function getJointMonthlyDisposableIncome(): Price;
    public function getJointMaximumTermAllowed(): int;
    public function getInterestRate(): ?float;
    public function getDownPaymentTerm(): ?int;
    public function getBalancePaymentTerm(): ?int;
}
