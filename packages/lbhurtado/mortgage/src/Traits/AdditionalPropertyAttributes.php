<?php

namespace LBHurtado\Mortgage\Traits;

use LBHurtado\Mortgage\Enums\Property\DevelopmentForm;
use LBHurtado\Mortgage\Enums\Property\DevelopmentType;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;
use Brick\Money\Money;

trait AdditionalPropertyAttributes
{
    const TOTAL_CONTRACT_PRICE = 'total_contract_price';
    const APPRAISAL_VALUE = 'appraisal_value';
    const PROCESSING_FEE = 'processing_fee';
    const PERCENT_LOANABLE_VALUE = 'percent_loanable_value';
    const PERCENT_DISPOSABLE_INCOME_REQUIREMENT = 'percent_disposable_income_requirement';
    const DEVELOPMENT_TYPE = 'development_type';
    const DEVELOPMENT_FORM = 'development_form';
    const REQUIRED_BUFFER_MARGIN = 'required_buffer_margin';
    const PERCENT_MISCELLANEOUS_FEES = 'percent_miscellaneous_fees';

    public function initializeAdditionalPropertyAttributes(): void
    {
        $this->mergeFillable([
            self::TOTAL_CONTRACT_PRICE,
            self::APPRAISAL_VALUE,
            self::PROCESSING_FEE,
            self::PERCENT_DISPOSABLE_INCOME_REQUIREMENT,
            self::PERCENT_LOANABLE_VALUE,
            self::DEVELOPMENT_TYPE,
            self::DEVELOPMENT_FORM,
            self::REQUIRED_BUFFER_MARGIN,
            self::PERCENT_MISCELLANEOUS_FEES,
        ]);
        $this->appends = array_merge($this->appends, [
            self::TOTAL_CONTRACT_PRICE,
            self::APPRAISAL_VALUE,
            self::PROCESSING_FEE,
            self::PERCENT_DISPOSABLE_INCOME_REQUIREMENT,
            self::PERCENT_LOANABLE_VALUE,
            self::DEVELOPMENT_TYPE,
            self::DEVELOPMENT_FORM,
            self::REQUIRED_BUFFER_MARGIN,
            self::PERCENT_MISCELLANEOUS_FEES,
        ]);
    }

    /**
     * Set total_contract_price.
     */
    public function setTotalContractPriceAttribute(Price|Money|float|int|string|null $value): self
    {
        if (is_null($value)) {
            $this->getAttribute('meta')->forget(self::TOTAL_CONTRACT_PRICE);
            return $this;
        }

        $money = $value instanceof Price ? $value : MoneyFactory::of($value);

        $this->getAttribute('meta')->set(self::TOTAL_CONTRACT_PRICE, $money->getAmount()->toFloat());

        return $this;
    }

    /**
     * Get total_contract_price.
     */
    public function getTotalContractPriceAttribute(): ?Price
    {

        $amount = $this->getAttribute('meta')->get(self::TOTAL_CONTRACT_PRICE);

        return $amount !== null ? MoneyFactory::price($amount)->setVat(0) : null;
    }

    /**
     * Get development_type.
     */
    public function getDevelopmentTypeAttribute(): ?DevelopmentType
    {
        $type = $this->getAttribute('meta')->get(self::DEVELOPMENT_TYPE);

        return $type !== null ? DevelopmentType::tryFrom($type) : null;
    }

    /**
     * Set development_type.
     */
    public function setDevelopmentTypeAttribute(DevelopmentType|string $value): static
    {
        $this->getAttribute('meta')->set(
            self::DEVELOPMENT_TYPE,
            $value instanceof DevelopmentType ? $value->value : (string) $value
        );

        return $this;
    }

    /**
     * Get development_form.
     */
    public function getDevelopmentFormAttribute(): ?DevelopmentForm
    {
        $form = $this->getAttribute('meta')->get(self::DEVELOPMENT_FORM);

        return $form !== null ? DevelopmentForm::from($form) : null;
    }

    /**
     * Set development_form.
     */
    public function setDevelopmentFormAttribute(DevelopmentForm|string $value): static
    {
        $this->getAttribute('meta')->set(self::DEVELOPMENT_FORM, $value instanceof DevelopmentForm ? $value->value : $value);

        return $this;
    }

    /**
     * Get required_buffer_margin.
     */
    public function getRequiredBufferMarginAttribute(): ?Percent
    {
        $margin = $this->getAttribute('meta')->get(self::REQUIRED_BUFFER_MARGIN);

        return $margin !== null ? Percent::ofFraction($margin) : null;
    }

    /**
     * Set required_buffer_margin.
     */
    public function setRequiredBufferMarginAttribute(Percent|int|float $value): static
    {
        $percent = match (true) {
            $value instanceof Percent       => $value,
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_int($value), is_float($value) => Percent::ofPercent($value),
            default                         => throw new \InvalidArgumentException('Invalid buffer margin.'),
        };

        $this->getAttribute('meta')->set(self::REQUIRED_BUFFER_MARGIN, $percent->value());

        return $this;
    }

    /**
     * Get percent_disposable_income_requirement.
     */
    public function getPercentDisposableIncomeRequirementAttribute(): ?Percent
    {
        $requirement = $this->getAttribute('meta')->get(self::PERCENT_DISPOSABLE_INCOME_REQUIREMENT);

        return $requirement !== null ? Percent::ofFraction($requirement) : null;
    }

    /**
     * Set percent_disposable_income_requirement.
     */
    public function setPercentDisposableIncomeRequirementAttribute(Percent|int|float $value): static
    {
        $percent = match (true) {
            $value instanceof Percent       => $value,
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_int($value), is_float($value) => Percent::ofPercent($value),
            default                         => throw new \InvalidArgumentException('Invalid disposable income requirement.'),
        };

        $this->getAttribute('meta')->set(self::PERCENT_DISPOSABLE_INCOME_REQUIREMENT, $percent->value());

        return $this;
    }

    /**
     * Get percent_loanable_value.
     */
    public function getPercentLoanableValueAttribute(): ?Percent
    {
        $value = $this->getAttribute('meta')->get(self::PERCENT_LOANABLE_VALUE);

        return $value !== null ? Percent::ofFraction($value) : null;
    }

    /**
     * Set percent_loanable_value.
     */
    public function setPercentLoanableValueAttribute(Percent|int|float $value): static
    {
        $percent = match (true) {
            $value instanceof Percent       => $value,
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_int($value), is_float($value) => Percent::ofPercent($value),
            default                         => throw new \InvalidArgumentException('Invalid loanable value percent.'),
        };

        $this->getAttribute('meta')->set(self::PERCENT_LOANABLE_VALUE, $percent->value());

        return $this;
    }

    /**
     * Get appraisal_value.
     */
    public function getAppraisalValueAttribute(): ?Price
    {
        $value = $this->getAttribute('meta')->get(self::APPRAISAL_VALUE);

        return $value !== null ? MoneyFactory::price($value) : null;
    }

    /**
     * Set appraisal_value.
     */
    public function setAppraisalValueAttribute(Price|Money|float|int|string|null $value): static
    {
        if (is_null($value)) {
            $this->getAttribute('meta')->forget(self::APPRAISAL_VALUE);
            return $this;
        }

        $price = $value instanceof Price ? $value : MoneyFactory::of($value);

        $this->getAttribute('meta')->set(self::APPRAISAL_VALUE, $price->getAmount()->toFloat());

        return $this;
    }

    /**
     * Get processing_fee.
     */
    public function getProcessingFeeAttribute(): ?Price
    {
        $fee = $this->getAttribute('meta')->get(self::PROCESSING_FEE);

        return $fee !== null ? MoneyFactory::price($fee) : null;
    }

    /**
     * Set processing_fee.
     */
    public function setProcessingFeeAttribute(Price|Money|float|int|string|null $value): static
    {
        if (is_null($value)) {
            $this->getAttribute('meta')->forget(self::PROCESSING_FEE);
            return $this;
        }

        $fee = $value instanceof Price ? $value : MoneyFactory::of($value);

        $this->getAttribute('meta')->set(self::PROCESSING_FEE, $fee->getAmount()->toFloat());

        return $this;
    }

    /**
     * Get percent_miscellaneous_fees.
     */
    public function getPercentMiscellaneousFeesAttribute(): ?Percent
    {
        $fee = $this->getAttribute('meta')->get(self::PERCENT_MISCELLANEOUS_FEES);

        return $fee !== null ? Percent::ofFraction($fee) : null;
    }

    /**
     * Set percent_miscellaneous_fees.
     */
    public function setPercentMiscellaneousFeesAttribute(Percent|int|float|null $value): static
    {
        $percent = match (true) {
            $value instanceof Percent       => $value,
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_int($value), is_float($value) => Percent::ofPercent($value),
            default                         => throw new \InvalidArgumentException('Invalid miscellaneous fees.'),
        };

        $this->getAttribute('meta')->set('percent_miscellaneous_fees', $percent->value());

        return $this;
    }
}
