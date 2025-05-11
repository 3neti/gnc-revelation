<?php

namespace LBHurtado\Mortgage\Classes;

use LBHurtado\Mortgage\Enums\Property\{DevelopmentForm, DevelopmentType, HousingType, MarketSegment};
use LBHurtado\Mortgage\Traits\HasFinancialAttributes;
use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;
use Brick\Money\Money;

class Property implements PropertyInterface
{
    use HasFinancialAttributes;

    protected Price $total_contract_price;
    protected DevelopmentType $development_type;
    protected DevelopmentForm $development_form;
    protected HousingType $housing_type;
    protected Percent $required_buffer_margin;
    protected ?Percent $income_requirement_multiplier;
    protected Percent $percent_loanable_value;
    protected ?Price $appraisal_value = null;
    protected ?Price $processing_fee = null;
    protected ?Percent $percent_miscellaneous_fees = null;

    public function __construct(
        float|int|string $total_contract_price,
        ?DevelopmentType $development_type = null,
        ?DevelopmentForm $development_form = null,
        ?HousingType $housing_type = null,
    ) {
        $this->setTotalContractPrice($total_contract_price);

        $this->setDevelopmentType(
            $development_type ?? DevelopmentType::from(
            config('gnc-revelation.property.default.development_type')
        )
        );

        $this->setDevelopmentForm(
            $development_form ?? DevelopmentForm::from(
            config('gnc-revelation.property.default.development_form')
        )
        );

        $this->setHousingType(
            $housing_type ?? HousingType::from(
            config('gnc-revelation.property.default.housing_type')
        )
        );


        $buffer = config('gnc-revelation.default_buffer_margin');

        if (is_null($buffer)) {
            throw new \RuntimeException("Default buffer margin must not be null.");
        }

        $this->setRequiredBufferMargin($buffer);
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

    public function setDevelopmentType(DevelopmentType $type): static
    {
        $this->development_type = $type;
        return $this;
    }

    public function getDevelopmentType(): DevelopmentType
    {
        return $this->development_type;
    }

    public function setDevelopmentForm(DevelopmentForm $form): static
    {
        $this->development_form = $form;
        return $this;
    }

    public function getDevelopmentForm(): DevelopmentForm
    {
        return $this->development_form;
    }

    public function setHousingType(HousingType $housingType): static
    {
        $this->housing_type = $housingType;
        return $this;
    }

    public function getHousingType(): HousingType
    {
        return $this->housing_type;
    }

    public function setRequiredBufferMargin(Percent|float|int $value): static
    {
        $this->required_buffer_margin = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            default                         => throw new \InvalidArgumentException("Invalid buffer margin."),
        };

        return $this;
    }

    public function getRequiredBufferMargin(): Percent
    {
        return $this->required_buffer_margin;
    }

    public function getMarketSegment(): MarketSegment
    {
        return MarketSegment::fromPrice($this->total_contract_price, $this->development_type);
    }

    public function setIncomeRequirementMultiplier(Percent|float|int|null $value): static
    {
        $this->income_requirement_multiplier = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            is_null($value)                 => null,
            default                         => throw new \InvalidArgumentException("Invalid value for disposable income requirement."),
        };

        return $this;
    }

    public function getIncomeRequirementMultiplier(): ?Percent
    {
        return $this->income_requirement_multiplier
            ?? $this->getMarketSegment()->defaultIncomeRequirementMultiplier();
    }

    public function setPercentLoanableValue(Percent|float|int $value): static
    {
        $this->percent_loanable_value = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            default                         => throw new \InvalidArgumentException("Invalid value for loanable value percent."),
        };

        return $this;
    }

    public function getPercentLoanableValue(): Percent
    {
        return $this->percent_loanable_value
            ?? $this->getMarketSegment()->defaultPercentLoanableValue();
    }

    public function setAppraisalValue(float|Money|Price|null $value): static
    {
        $this->appraisal_value = match (true) {
            $value instanceof Price => $value,
            $value instanceof Money => MoneyFactory::price($value),
            is_numeric($value)      => MoneyFactory::price($value),
            is_null($value)         => null,
            default => throw new \InvalidArgumentException("Invalid value for appraisal price."),
        };

        return $this;
    }

    public function getAppraisalValue(): ?Price
    {
        return $this->appraisal_value;
    }

    public function getLoanableAmount(): Price
    {
        $baseValue = $this->appraisal_value?->inclusive()->getAmount()->toFloat()
            ?? $this->total_contract_price->inclusive()->getAmount()->toFloat();

        $multiplier = $this->getPercentLoanableValue()->value();

        return MoneyFactory::price($baseValue * $multiplier);
    }

    public function setProcessingFee(float|Money|Price|null $value): static
    {
        $this->processing_fee = match (true) {
            $value instanceof Price => $value,
            $value instanceof Money => MoneyFactory::price($value),
            is_numeric($value)      => MoneyFactory::price($value),
            is_null($value)         => null,
            default                 => throw new \InvalidArgumentException('Invalid processing fee.'),
        };

        return $this;
    }

    public function getProcessingFee(): ?Price
    {
        return $this->processing_fee;
    }

    public function setPercentMiscellaneousFees(Percent|float|int|null $value): static
    {
        $this->percent_miscellaneous_fees = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            is_null($value)                 => null,
            default                         => throw new \InvalidArgumentException("Invalid value for miscellaneous fees percent."),
        };

        return $this;
    }

    public function getPercentMiscellaneousFees(): ?Percent
    {
        return $this->percent_miscellaneous_fees;
    }

    /**
     * Provides the default interest rate based on market segment and contract price.
     * Used by HasFinancialAttributes if no explicit interest rate is set.
     */
    public function resolveDefaultInterestRate(): Percent
    {
        return $this->getMarketSegment()->defaultInterestRateFor($this->getTotalContractPrice());
    }

    protected string $code = 'N/A';

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
