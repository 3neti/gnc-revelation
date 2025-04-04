<?php

namespace Tests\Fakes;

use App\Contracts\OrderInterface;
use Whitecube\Price\Price;

class FlexibleFakeOrder implements OrderInterface
{
    public function __construct(
        protected ?float $interest = null,
        protected ?float $percent_down_payment = 0.0,
        protected ?float $income_requirement_multiplier = 0.35,
        protected ?float $mri = 0.0,
        protected ?float $fire = 0.0
    ) {}

    public function getInterestRate(): ?float { return $this->interest; }
    public function getPercentDownPayment(): ?float { return $this->percent_down_payment; }
    public function getIncomeRequirementMultiplier(): ?float { return $this->income_requirement_multiplier; }
    public function getMortgageRedemptionInsurance(): ?float { return $this->mri; }
    public function getAnnualFireInsurance(): ?float { return $this->fire; }

    public function getDiscountAmount(): ?Price { return null; }
    public function getDownPaymentTerm(): ?int { return null; }
    public function getBalancePaymentTerm(): ?int { return null; }
    public function getLowCashOut(): ?Price { return null; }
    public function getPercentMiscellaneousFees(): ?float { return null; }
    public function getConsultingFee(): ?Price { return null; }
    public function getProcessingFee(): ?Price { return null; }
    public function getWaivedProcessingFee(): ?Price { return null; }
}
