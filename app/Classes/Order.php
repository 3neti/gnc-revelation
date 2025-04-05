<?php

namespace App\Classes;

use App\Contracts\OrderInterface;
use App\Support\MoneyFactory;
use Whitecube\Price\Price;

class Order implements OrderInterface
{
    protected ?float $interestRate = null;
    protected ?float $percentDownPayment = null;
    protected ?float $incomeRequirementMultiplier = null;
    protected ?float $monthlyMRI = null;
    protected ?float $monthlyFI = null;
    protected ?float $percentMiscellaneousFees = null;

    protected ?Price $discountAmount = null;
    protected ?Price $lowCashOut = null;
    protected ?Price $consultingFee = null;
    protected ?Price $processingFee = null;
    protected ?Price $waivedProcessingFee = null;

    protected ?int $dpTerm = null;
    protected ?int $bpTerm = null;

    public function getInterestRate(): ?float
    {
        return $this->interestRate;
    }

    public function setInterestRate(?float $rate): static
    {
        $this->interestRate = $rate;
        return $this;
    }

    public function getPercentDownPayment(): ?float
    {
        return $this->percentDownPayment;
    }

    public function setPercentDownPayment(?float $value): static
    {
        $this->percentDownPayment = $value;
        return $this;
    }

    public function getIncomeRequirementMultiplier(): ?float
    {
        return $this->incomeRequirementMultiplier;
    }

    public function setIncomeRequirementMultiplier(?float $value): static
    {
        $this->incomeRequirementMultiplier = $value;
        return $this;
    }

    public function getMonthlyMRI(): ?float
    {
        return $this->monthlyMRI;
    }

    public function setMonthlyMRI(?float $value): static
    {
        $this->monthlyMRI = $value;
        return $this;
    }

    public function getMonthlyFI(): ?float
    {
        return $this->monthlyFI;
    }

    public function setMonthlyFI(?float $value): static
    {
        $this->monthlyFI = $value;
        return $this;
    }

    public function getPercentMiscellaneousFees(): ?float
    {
        return $this->percentMiscellaneousFees;
    }

    public function setPercentMiscellaneousFees(?float $value): static
    {
        $this->percentMiscellaneousFees = $value;
        return $this;
    }

    public function getDiscountAmount(): ?Price
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(?float $value): static
    {
        $this->discountAmount = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getLowCashOut(): ?Price
    {
        return $this->lowCashOut;
    }

    public function setLowCashOut(?float $value): static
    {
        $this->lowCashOut = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getConsultingFee(): ?Price
    {
        return $this->consultingFee;
    }

    public function setConsultingFee(?float $value): static
    {
        $this->consultingFee = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getProcessingFee(): ?Price
    {
        return $this->processingFee;
    }

    public function setProcessingFee(?float $value): static
    {
        $this->processingFee = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getWaivedProcessingFee(): ?Price
    {
        return $this->waivedProcessingFee;
    }

    public function setWaivedProcessingFee(?float $value): static
    {
        $this->waivedProcessingFee = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getDownPaymentTerm(): ?int
    {
        return $this->dpTerm;
    }

    public function setDownPaymentTerm(?int $months): static
    {
        $this->dpTerm = $months;
        return $this;
    }

    public function getBalancePaymentTerm(): ?int
    {
        return $this->bpTerm;
    }

    public function setBalancePaymentTerm(?int $months): static
    {
        $this->bpTerm = $months;
        return $this;
    }
}
