<?php

namespace LBHurtado\Mortgage\Contracts;

use LBHurtado\Mortgage\Enums\MonthlyFee;
use LBHurtado\Mortgage\ValueObjects\FeeCollection;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

interface OrderInterface
{
    public function getInterestRate(): ?Percent;
    public function getPercentDownPayment(): ?Percent;
    public function getIncomeRequirementMultiplier(): ?Percent;

    public function getDiscountAmount(): ?Price;
    public function getLowCashOut(): ?Price;
    public function getConsultingFee(): ?Price;
    public function getProcessingFee(): ?Price;
    public function getWaivedProcessingFee(): ?Price;

    public function getDownPaymentTerm(): ?int;
    public function getBalancePaymentTerm(): ?int;

    public function getPercentMiscellaneousFees(): Percent;

    // New structure for monthly fees
    public function getMonthlyFees(): FeeCollection;
    public function setMonthlyFee(MonthlyFee $fee, float $amount): static;
    public function getMonthlyFee(MonthlyFee $fee): ?float;
}
