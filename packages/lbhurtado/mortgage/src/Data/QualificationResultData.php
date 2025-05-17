<?php

namespace LBHurtado\Mortgage\Data;

use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\Enums\CalculatorType;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Spatie\LaravelData\Attributes\WithCast;
use LBHurtado\Mortgage\Casts\PriceCast;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

/** @deprecated  */
class QualificationResultData extends Data
{
    public function __construct(
        public bool $qualifies,
        public string $reason,
        public Price $loan_difference,
        public Price $income_gap,
        public Percent $suggested_down_payment_percent,

        #[WithCast(PriceCast::class)]
        public Price $income_required,

        #[WithCast(PriceCast::class)]
        public Price $monthly_amortization,

        public MortgageResultData $mortgage
    ) {}

    /** TODO: improve reason */
    public static function fromInputs(InputsData $inputs): static
    {
        return new static(
            qualifies: $qualifies = CalculatorFactory::make(CalculatorType::LOAN_QUALIFICATION, $inputs)->calculate(),
            reason: $qualifies ? 'Sufficient disposable income' : 'Disposable income below amortization',
            loan_difference: CalculatorFactory::make(CalculatorType::EQUITY, $inputs)->calculate()->toPrice(),
            income_gap: CalculatorFactory::make(CalculatorType::INCOME_GAP, $inputs)->calculate(),
            suggested_down_payment_percent: CalculatorFactory::make(CalculatorType::REQUIRED_PERCENT_DOWN_PAYMENT, $inputs)->calculate(),
            income_required: CalculatorFactory::make(CalculatorType::REQUIRED_INCOME, $inputs)->calculate(),
            monthly_amortization: CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->total(),
            mortgage: MortgageResultData::fromInputs($inputs),
        );
    }
}
