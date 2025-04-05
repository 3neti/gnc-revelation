<?php

namespace App\Classes;

use App\Enums\Property\DevelopmentType;
use App\Enums\Property\MarketSegment;
use App\Contracts\PropertyInterface;
use App\Support\MoneyFactory;
use Whitecube\Price\Price;
use Brick\Money\Money;

class Property implements PropertyInterface
{
    protected Price $total_contract_price;
    protected ?float $appraisal_value = null;
    protected ?float $loanable_value_multiplier = null;
    protected ?float $disposable_income_multiplier = null;
    protected ?float $interest_rate = null;
    protected ?float $buffer_margin = null;
    protected ?float $income_requirement_multiplier = null;
    protected ?float $percent_misc_fees = null;
    protected DevelopmentType $development_type;

    public function __construct()
    {
        $this->development_type = DevelopmentType::BP_220;
        $this->buffer_margin = config('gnc-revelation.default_buffer_margin', 0.1);
        $this->percent_misc_fees = config('gnc-revelation.property.default.percent_mf', 0.085);
    }

    public function setTotalContractPrice(float|Money|Price $value): static
    {
        $this->total_contract_price = $value instanceof Price
            ? $value
            : MoneyFactory::price($value);

        return $this;
    }

    public function getTotalContractPrice(): Price
    {
        return $this->total_contract_price;
    }

    public function setAppraisalValue(?float $value): static
    {
        $this->appraisal_value = $value;
        return $this;
    }

    public function getAppraisalValue(): ?float
    {
        return $this->appraisal_value;
    }

    public function getLoanableAmount(): Price
    {
        $tcp = $this->total_contract_price->inclusive()->getAmount()->toFloat();
        $appraisal = $this->appraisal_value ?? $tcp;
        $base = min($appraisal, $tcp);
        $multiplier = $this->getPercentLoanable();

        return MoneyFactory::price($base * $multiplier);
    }

    public function getInterestRate(): ?float
    {
        return $this->interest_rate ?? $this->getDefaultInterestRate();
    }

    public function setInterestRate(?float $value): static
    {
        $this->interest_rate = $value;
        return $this;
    }

    public function getRequiredBufferMargin(): ?float
    {
        return $this->buffer_margin;
    }

    public function setRequiredBufferMargin(?float $value): static
    {
        $this->buffer_margin = $value;
        return $this;
    }

    public function getIncomeRequirementMultiplier(): ?float
    {
        return $this->income_requirement_multiplier
            ?? $this->getMarketSegment()->defaultDisposableIncomeRequirementMultiplier();
    }

    public function setIncomeRequirementMultiplier(?float $value): static
    {
        $this->income_requirement_multiplier = $value;
        return $this;
    }

    public function getPercentLoanable(): ?float
    {
        return $this->loanable_value_multiplier
            ?? $this->getMarketSegment()->defaultLoanableValueMultiplier();
    }

    public function setPercentLoanable(?float $value): static
    {
        $this->loanable_value_multiplier = $value;
        return $this;
    }

    public function setDevelopmentType(DevelopmentType $type): static
    {
        $this->development_type = $type;
        return $this;
    }

    public function getDevelopmentType(): DevelopmentType
    {
        return $this->development_type;
    }

    public function getMarketSegment(): MarketSegment
    {
        return MarketSegment::fromPrice($this->total_contract_price, $this->development_type);
    }

    public function getProcessingFee(): ?Price
    {
        $amount = config('gnc-revelation.property.default.processing_fee', 10000);
        return MoneyFactory::price($amount);
    }

    public function getPercentMiscellaneousFees(): ?float
    {
        return $this->percent_misc_fees;
    }

    public function setPercentMiscellaneousFees(?float $value): static
    {
        $this->percent_misc_fees = $value;
        return $this;
    }

    protected function getDefaultInterestRate(): float
    {
        $tcp = $this->total_contract_price->inclusive()->getAmount()->toFloat();

        return match ($this->getMarketSegment()) {
            MarketSegment::SOCIALIZED, MarketSegment::ECONOMIC => match (true) {
                $tcp <= 750_000 => 0.03,
                $tcp <= 850_000 => 0.0625,
                default => 0.0625,
            },
            MarketSegment::OPEN => 0.07,
        };
    }
}
