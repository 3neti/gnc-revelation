<?php

namespace Tests\Fakes;

use LBHurtado\Mortgage\Contracts\OrderInterface;
use Whitecube\Price\Price;

class FlexibleFakeOrder implements OrderInterface
{
    public function __construct(
        protected ?float $interest = 0.0,
        protected ?float $percent_down_payment = 0.0,
        protected ?float $income_requirement_multiplier = 0.35,
        protected ?float $percent_miscellaneous_fees = 0.0,
        protected ?float $monthly_mri = 0.0,
        protected ?float $monthly_fi = 0.0,
    ) {}

    public function getInterestRate(): ?float { return $this->interest; }
    public function getPercentDownPayment(): float { return $this->percent_down_payment; }
    public function getIncomeRequirementMultiplier(): ?float { return $this->income_requirement_multiplier; }
    public function getMonthlyMRI(): ?float { return $this->monthly_mri; }
    public function getMonthlyFI(): ?float { return $this->monthly_fi; }

    public function getDiscountAmount(): ?Price { return null; }
    public function getDownPaymentTerm(): ?int { return null; }
    public function getBalancePaymentTerm(): ?int { return null; }
    public function getLowCashOut(): ?Price { return null; }
    public function getPercentMiscellaneousFees(): float { return $this->percent_miscellaneous_fees ?? 0.0; }
    public function getConsultingFee(): ?Price { return null; }
    public function getProcessingFee(): ?Price { return null; }
    public function getWaivedProcessingFee(): ?Price { return null; }
}
