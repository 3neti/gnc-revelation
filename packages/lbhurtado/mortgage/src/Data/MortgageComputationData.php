<?php

namespace LBHurtado\Mortgage\Data;

use LBHurtado\Mortgage\Data\Transformers\{LendingInstitutionToStringTransformer, PercentToFloatTransformer, PriceToFloatTransformer};
use LBHurtado\Mortgage\Casts\{LendingInstitutionCast, PercentCast, PriceCast};
use LBHurtado\Mortgage\Factories\{CalculatorFactory, ExtractorFactory};
use Spatie\LaravelData\Attributes\{WithCast, WithTransformer};
use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType};
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class MortgageComputationData extends Data
{
    public function __construct(
        #[WithTransformer(LendingInstitutionToStringTransformer::class)]
        #[WithCast(LendingInstitutionCast::class)]
        public LendingInstitution                           $lending_institution,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PercentCast::class)]
        public Percent                                      $interest_rate,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PercentCast::class)]
        public Percent                                      $percent_down_payment,

        #[WithTransformer(PriceToFloatTransformer::class)]
        public Percent                                      $percent_miscellaneous_fees,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $total_contract_price,

        #[WithTransformer(PercentToFloatTransformer::class)]
        #[WithCast(PercentCast::class)]
        public Percent                                      $income_requirement_multiplier,

        #[WithTransformer(PercentToFloatTransformer::class)]
        #[WithCast(PercentCast::class)]
        public int                                          $balance_payment_term,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $monthly_disposable_income,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $present_value,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $loanable_amount,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $required_equity,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $monthly_amortization,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $miscellaneous_fees,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $add_on_fees,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $cash_out,

        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price                                        $income_gap,

        #[WithTransformer(PercentToFloatTransformer::class)]
        #[WithCast(PercentCast::class)]
        public Percent                                      $percent_down_payment_remedy,

        public MortgageParticulars                          $inputs,
    ) {}

    public static function fromParticulars(MortgageParticulars $mortgage_particulars): static
    {
        return new static(
            lending_institution: ExtractorFactory::make(ExtractorType::LENDING_INSTITUTION, $mortgage_particulars)->extract(),
            interest_rate: ExtractorFactory::make(ExtractorType::INTEREST_RATE, $mortgage_particulars)->extract(),
            percent_down_payment: ExtractorFactory::make(ExtractorType::PERCENT_DOWN_PAYMENT, $mortgage_particulars)->extract(),
            percent_miscellaneous_fees: ExtractorFactory::make(ExtractorType::PERCENT_MISCELLANEOUS_FEES, $mortgage_particulars)->extract(),
            total_contract_price: ExtractorFactory::make(ExtractorType::TOTAL_CONTRACT_PRICE, $mortgage_particulars)->extract(),
            income_requirement_multiplier: ExtractorFactory::make(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER, $mortgage_particulars)->extract(),
            balance_payment_term: CalculatorFactory::make(CalculatorType::BALANCE_PAYMENT_TERM, $mortgage_particulars)->calculate(),
            monthly_disposable_income: CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $mortgage_particulars)->calculate(),
            present_value: CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $mortgage_particulars)->calculate(),
            loanable_amount: CalculatorFactory::make(CalculatorType::LOAN_AMOUNT, $mortgage_particulars)->calculate(),
            required_equity: CalculatorFactory::make(CalculatorType::EQUITY, $mortgage_particulars)->calculate()->toPrice(),
            monthly_amortization: CalculatorFactory::make(CalculatorType::AMORTIZATION, $mortgage_particulars)->total(),
            miscellaneous_fees: CalculatorFactory::make(CalculatorType::MISCELLANEOUS_FEES, $mortgage_particulars)->calculate(),
            add_on_fees: CalculatorFactory::make(CalculatorType::FEES, $mortgage_particulars)->total(),
            cash_out: CalculatorFactory::make(CalculatorType::CASH_OUT, $mortgage_particulars)->calculate()->total,
            income_gap: CalculatorFactory::make(CalculatorType::INCOME_GAP, $mortgage_particulars)->calculate(),
            percent_down_payment_remedy: CalculatorFactory::make(CalculatorType::REQUIRED_PERCENT_DOWN_PAYMENT, $mortgage_particulars)->calculate(),
            inputs: $mortgage_particulars,
        );
    }

    public function qualifies(): bool
    {
        return CalculatorFactory::make(CalculatorType::LOAN_QUALIFICATION, $this->inputs)->calculate();
    }

    public function reason(): string
    {
        return $this->qualifies() ?
            'Sufficient disposable income' :
            'Disposable income below amortization';
    }
}
