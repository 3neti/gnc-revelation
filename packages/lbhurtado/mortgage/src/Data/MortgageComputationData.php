<?php

namespace LBHurtado\Mortgage\Data;

use LBHurtado\Mortgage\Data\Transformers\LendingInstitutionToStringTransformer;
use LBHurtado\Mortgage\Factories\{CalculatorFactory, ExtractorFactory};
use LBHurtado\Mortgage\Data\Transformers\PercentToFloatTransformer;
use LBHurtado\Mortgage\Data\Transformers\PriceToFloatTransformer;
use Spatie\LaravelData\Attributes\{WithCast, WithTransformer};
use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType};
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Casts\PriceCast;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class MortgageComputationData extends Data
{
    public function __construct(
        #[WithTransformer(LendingInstitutionToStringTransformer::class)]
        public LendingInstitution $lending_institution,

        #[WithTransformer(PriceToFloatTransformer::class)]
        public Percent $interest_rate,

        #[WithTransformer(PriceToFloatTransformer::class)]
        public Percent $percent_down_payment,

        #[WithTransformer(PriceToFloatTransformer::class)]
        public Percent $percent_miscellaneous_fees,

        #[WithCast(PriceCast::class)]
        public Price $total_contract_price,

        #[WithTransformer(PercentToFloatTransformer::class)]
        public Percent $income_requirement_multiplier,

        #[WithTransformer(PercentToFloatTransformer::class)]
        public int $balance_payment_term,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price $monthly_disposable_income,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price $present_value,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price $loanable_amount,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price $required_equity,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price $monthly_amortization,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price  $miscellaneous_fees,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price $add_on_fees,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price $cash_out,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price $income_gap,

        #[WithTransformer(PercentToFloatTransformer::class)]
        public Percent $percent_down_payment_remedy,

        public InputsData $inputs,
    ) {}

    public static function fromInputs(InputsData $inputs): static
    {
        return new static(
            lending_institution: ExtractorFactory::make(ExtractorType::LENDING_INSTITUTION, $inputs)->extract(),
            interest_rate: ExtractorFactory::make(ExtractorType::INTEREST_RATE, $inputs)->extract(),
            percent_down_payment: ExtractorFactory::make(ExtractorType::PERCENT_DOWN_PAYMENT, $inputs)->extract(),
            percent_miscellaneous_fees: ExtractorFactory::make(ExtractorType::PERCENT_MISCELLANEOUS_FEES, $inputs)->extract(),
            total_contract_price: ExtractorFactory::make(ExtractorType::TOTAL_CONTRACT_PRICE, $inputs)->extract(),
            income_requirement_multiplier: ExtractorFactory::make(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER, $inputs)->extract(),
            balance_payment_term: CalculatorFactory::make(CalculatorType::BALANCE_PAYMENT_TERM, $inputs)->calculate(),
            monthly_disposable_income: CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $inputs)->calculate(),
            present_value: CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $inputs)->calculate(),
            loanable_amount: CalculatorFactory::make(CalculatorType::LOAN_AMOUNT, $inputs)->calculate(),
            required_equity: CalculatorFactory::make(CalculatorType::EQUITY, $inputs)->calculate()->toPrice(),
            monthly_amortization: CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->total(),
            miscellaneous_fees: CalculatorFactory::make(CalculatorType::MISCELLANEOUS_FEES, $inputs)->calculate(),
            add_on_fees: CalculatorFactory::make(CalculatorType::FEES, $inputs)->total(),
            cash_out: CalculatorFactory::make(CalculatorType::CASH_OUT, $inputs)->calculate()->total,
            income_gap: CalculatorFactory::make(CalculatorType::INCOME_GAP, $inputs)->calculate(),
            percent_down_payment_remedy: CalculatorFactory::make(CalculatorType::REQUIRED_PERCENT_DOWN_PAYMENT, $inputs)->calculate(),
            inputs: $inputs,
        );
    }
}
