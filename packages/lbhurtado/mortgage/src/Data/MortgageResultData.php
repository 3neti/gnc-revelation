<?php

namespace LBHurtado\Mortgage\Data;

use LBHurtado\Mortgage\ValueObjects\MiscellaneousFee;
use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\Enums\CalculatorType;
use Spatie\LaravelData\Attributes\WithCast;
use LBHurtado\Mortgage\Casts\PriceCast;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class MortgageResultData extends Data
{
    public function __construct(
        public InputsData $inputs,

        public int $term_years,

        #[WithCast(PriceCast::class)]
        public Price $monthly_disposable_income,

        #[WithCast(PriceCast::class)]
        public Price $present_value,

        #[WithCast(PriceCast::class)]
        public Price $required_equity,

        #[WithCast(PriceCast::class)]
        public Price $monthly_amortization,

        #[WithCast(PriceCast::class)]
        public Price $add_on_fees,

        #[WithCast(PriceCast::class)]
        public Price $cash_out,

        #[WithCast(PriceCast::class)]
        public Price $loanable_amount,

        #[WithCast(PriceCast::class)]
        public Price $miscellaneous_fee,
    ) {}

    public static function fromInputs(InputsData $inputs): static
    {
        return new static(
            inputs: $inputs,
            term_years: $inputs->buyer()->getJointMaximumTermAllowed(),
            monthly_disposable_income: CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $inputs)->calculate(),
            present_value: CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $inputs)->calculate(),
            required_equity: CalculatorFactory::make(CalculatorType::EQUITY, $inputs)->calculate()->amount,
            monthly_amortization: CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->total(),
            add_on_fees: CalculatorFactory::make(CalculatorType::FEES, $inputs)->total(),
            cash_out: CalculatorFactory::make(CalculatorType::CASH_OUT, $inputs)->calculate()->total,
            loanable_amount: CalculatorFactory::make(CalculatorType::LOANABLE_AMOUNT, $inputs)->calculate(),
            miscellaneous_fee: MoneyFactory::price(MiscellaneousFee::fromInputs($inputs)->total()),
        );
    }
}
