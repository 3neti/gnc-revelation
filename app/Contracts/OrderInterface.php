<?php

namespace App\Contracts;

use Whitecube\Price\Price;

interface OrderInterface
{
    public function getInterestRate(): ?float;
    public function getPercentDownPayment(): ?float;
    public function getIncomeRequirementMultiplier(): ?float;
    public function getDiscountAmount(): ?Price;
    public function getDownPaymentTerm(): ?int;
    public function getBalancePaymentTerm(): ?int;
    public function getLowCashOut(): ?Price;
    public function getMonthlyMRI(): ?float;
    public function getMonthlyFI(): ?float;
    public function getPercentMiscellaneousFees(): ?float;
    public function getConsultingFee(): ?Price;
    public function getProcessingFee(): ?Price;
    public function getWaivedProcessingFee(): ?Price;
}
