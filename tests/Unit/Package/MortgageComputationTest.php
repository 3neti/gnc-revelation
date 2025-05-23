<?php

use LBHurtado\Mortgage\Factories\{CalculatorFactory, ExtractorFactory, MortgageParticularsFactory};
use LBHurtado\Mortgage\Classes\{Buyer, LendingInstitution, Order, Property};
use LBHurtado\Mortgage\Services\{AgeService, BorrowingRulesService};
use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType};
use LBHurtado\Mortgage\Data\Inputs\MortgageInputsData;
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\Data\MortgageComputationData;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

beforeEach(function () {
    $this->rules = new BorrowingRulesService(new AgeService());
});

dataset('simple amortization', [
    /******************************************************************************************************************************* lender     TCP    age1  gmi1  age2   gmi2 income interest %dp    %mf      pf       MRI?   FI?  term %gmi disposable      PV        equity    amortization  fees   cash out  loanable amount   mf     income gap  %dpr ****/
    /** start HDMF scenario with deficiency */
    'hdmf 1.0M in 21 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 49, 17_000, 00, 17_000, 0_000,  null, null,  null, 00_000.00, false, false, 21, 0.35,  5_950.0,   833_878.13, 166_121.87,  7_135.34,   0.00,       0.00, 1_000_000.00, 00_000.00, 1_185.34, 0.17, 20_386.69 ],
        /** 17% down payment remedy */
    'hdmf 1.0M in 21 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; 17% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 49, 17_000, 00, 17_000, 0_000,  null,  0.17, null, 00_000.00, false, false, 21, 0.35,  5_950.0,   833_878.13,        0.0,  5_922.33,   0.00,       170_000.0, 830_000.00, 00_000.00,  0.00,  0.17, 16_920.94 ],
        /** additional income  remedy */
    'hdmf 1.0M in 21 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/  +inc' => [ 'hdmf', 1_000_000, 49, 17_000, 00, 17_000, 3_386,  null, null,  null, 00_000.00, false, false, 21, 0.35,  7_135.1,   999_967.03,      32.97,  7_135.34,   0.00,       0.00, 1_000_000.00, 00_000.00,     0.24,  0.00, 20_386.69 ],

    'hdmf 1.0M in 23 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 47, 21_000, 00, 21_000, 0_000,  null, null, 0.000, 00_000.00, false, false, 23, 0.35,  7_350.0, 1_074_757.85,       0.00,  6_838.75,   0.00,       0.00, 1_000_000.00, 00_000.00,     0.00, 0.00, 19_539.29 ],
    'hdmf 1.1M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_100_000, 48, 19_000, 00, 19_000, 0_000,  null, null, 0.000, 00_000.00, false, false, 22, 0.35,  6_650.0,   952_820.39, 147_179.61,  7_677.21,   0.00,       0.00, 1_100_000.00, 00_000.00, 1_027.21, 0.14, 21_934.89 ],
    'hdmf 1.2M in 23 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_200_000, 47, 21_000, 00, 21_000, 0_000,  null, null, 0.000, 00_000.00, false, false, 23, 0.35,  7_350.0, 1_074_757.85, 125_242.15,  8_206.50,   0.00,       0.00, 1_200_000.00, 00_000.00,   856.50, 0.11, 23_447.14 ],

    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0_000,  null, 0.10, 0.000, 00_000.00, false, false, 22, 0.35,  6_650.0,   952_820.39,       0.00,   6281.35,   0.00, 100_000.00,   900_000.00, 00_000.00,     0.00, 0.05, 17_946.71 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0_000,  null, 0.10, 0.085, 00_000.00, false, false, 22, 0.35,  6_650.0,   952_820.39,  32_179.61,  6_874.59,   0.00, 100_000.00,   985_000.00, 85_000.00,   224.59, 0.14, 19_641.69 ],
    /** end working */
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ _0k pf no add-ons w/  co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 50, 19_000, 0_000,  null, 0.10, 0.085, 00_000.00, false, false, 20, 0.35, 13_300.0, 1_819_604.16,       0.00,  7_199.64,   0.00, 100_000.00,   985_000.00, 85_000.00,     0.00, 0.00, 20_570.4 ],
    'hdmf 1.0M in 20 yrs @ 6.25% by a 50yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ _0k pf no add-ons w/o co-borrower w/ +inc' =>  [ 'hdmf', 1_000_000, 50, 19_000, 00, 19_000,19_000,  null, 0.10, 0.085, 00_000.00, false, false, 20, 0.35, 13_300.0, 1_819_604.16,       0.00,  7_199.64,   0.00, 100_000.00,   985_000.00, 85_000.00,    0.00,  0.00, 20_570.4 ],

    /** start working */
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ 10k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0_000,  null, 0.10, 0.085, 10_000.00, false, false, 22, 0.35,  6_650.0,   952_820.39,  32_179.61,  6_874.59,   0.00, 110_000.00,   985_000.00, 85_000.00,   224.59, 0.14, 19_641.69 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ 10k pf mri and fi w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0_000,  null, 0.10, 0.085, 10_000.00,  true,  true, 22, 0.35,  6_650.0,   952_820.39,  32_179.61,  7_276.74, 402.15, 110_000.00,   985_000.00, 85_000.00,   626.74, 0.14, 20_790.69 ],
    'hdmf 1.3M in 24 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_300_000, 46, 23_000, 00, 23_000, 0_000,  null, null, 0.000, 00_000.00, false, false, 24, 0.35,  8_050.0, 1_199_384.92, 100_615.08,  8_725.31,   0.00,       0.00, 1_300_000.00, 00_000.00,   675.31, 0.08, 24_929.46 ],
    'hdmf 1.4M in 25 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_400_000, 45, 25_000, 00, 25_000, 0_000,  null, null, 0.000, 00_000.00, false, false, 25, 0.35,  8_750.0, 1_326_422.04,  73_577.96,  9_235.37,   0.00,       0.00, 1_400_000.00, 00_000.00,   485.37, 0.06, 26_386.77 ],
    /** end working */

    /** start RCBC scenario with deficiency */
    /** 0% down payment is imposed from RCBC */
    'rcbc 1.0M in 15 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_000_000, 49, 17_000, 00, 17_000, 0_000,    null, 0.00, 0.000, 00_000.00, false, false, 15, 0.35,  5_950.0,   693_939.97, 306_060.03,  8_574.23,   0.00,       0.00, 1_000_000.00, 00_000.00, 2_624.23, 0.31, 24_497.80 ],
    /** 10% down payment is default from RCBC */
    'rcbc 1.0M in 15 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_000_000, 49, 17_000, 00, 17_000, 0_000,    null, null, 0.000, 00_000.00, false, false, 15, 0.35,  5_950.0,   693_939.97, 206_060.03,  7_716.81,   0.00,  100_000.0,   900_000.00, 00_000.00, 1_766.81, 0.31, 22_048.03 ],
    /** 21% down payment remedy does not satisfy it*/
    'rcbc 1.0M in 15 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; 21% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_000_000, 49, 17_000, 00, 17_000, 0_000,    null, 0.21, 0.000, 00_000.00, false, false, 15, 0.35,  5_950.0,   693_939.97,  96_060.03, 6_773.64,    0.00,  210_000.00, 790_000.00,  00_000.00,   823.64, 0.31, 19_353.26 ],
    /** 31% down payment remedy does satisfy it*/
    'rcbc 1.0M in 15 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; 31% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_000_000, 49, 17_000, 00, 17_000, 0_000,    null, 0.31, 0.000, 00_000.00, false, false, 15, 0.35,  5_950.0,   693_939.97,       0.00,  5_916.22,   0.00,  310_000.00, 690_000.00,  00_000.00,     0.00, 0.31, 16_903.49 ],

    'rcbc 1.1M in 16 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_100_000, 48, 19_000, 00, 19_000, 0_000,    null, 0.00, 0.000, 00_000.00, false, false, 16, 0.35,  6_650.0,   805_870.98, 294_129.02,  9_077.14,   0.00,       0.00, 1_100_000.00, 00_000.00, 2_427.14, 0.27, 25_934.69 ],
    'rcbc 1.1M in 16 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_100_000, 48, 19_000, 00, 19_000, 0_000,    null, null, 0.000, 00_000.00, false, false, 16, 0.35,  6_650.0,   805_870.98, 184_129.02,  8_169.42,   0.00,  110_000.0,   990_000.00, 00_000.00, 1_519.42, 0.27, 23_341.20 ],
    'rcbc 1.2M in 17 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_200_000, 47, 21_000, 00, 21_000, 0_000,    null, 0.00, 0.000, 00_000.00, false, false, 17, 0.35,  7_350.0,   922_155.72, 277_844.28,  9_564.55,   0.00,       0.00, 1_200_000.00, 00_000.00, 2_214.55, 0.24, 27_327.29 ],
    'rcbc 1.2M in 17 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_200_000, 47, 21_000, 00, 21_000, 0_000,    null, null, 0.000, 00_000.00, false, false, 17, 0.35,  7_350.0,   922_155.72, 157_844.28,  8_608.09,   0.00,  120_000.0, 1_080_000.00, 00_000.00, 1_258.09, 0.24, 24_594.54 ],
    'rcbc 1.3M in 18 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_300_000, 46, 23_000, 00, 23_000, 0_000,    null, 0.00, 0.000, 00_000.00, false, false, 18, 0.35,  8_050.0, 1_042_350.02, 257_649.98, 10_039.81,   0.00,       0.00, 1_300_000.00, 00_000.00, 1_989.81, 0.20, 28_685.17 ],
    'rcbc 1.3M in 18 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_300_000, 46, 23_000, 00, 23_000, 0_000,    null, null, 0.000, 00_000.00, false, false, 18, 0.35,  8_050.0, 1_042_350.02, 127_649.98,  9_035.83,   0.00,  130_000.0, 1_170_000.00, 00_000.00,   985.83, 0.20, 25_816.66 ],
    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_400_000, 45, 25_000, 00, 25_000, 0_000,    null, 0.00, 0.000, 00_000.00, false, false, 19, 0.35,  8_750.0, 1_166_047.51, 233_952.49, 10_505.58,   0.00,       0.00, 1_400_000.00, 00_000.00, 1_755.58, 0.17, 30_015.94 ],
    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_400_000, 45, 25_000, 00, 25_000, 0_000,    null, null, 0.000, 00_000.00, false, false, 19, 0.35,  8_750.0, 1_166_047.51,  93_952.49,  9_455.02,   0.00,  140_000.0, 1_260_000.00, 00_000.00,   705.02, 0.17, 27_014.34 ],
    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/  co-borrower w/o +inc' => [ 'rcbc', 1_400_000, 45, 25_000, 50, 25_000, 0_000,    null, 0.00, 0.000, 00_000.00, false, false, 14, 0.35, 17_500.0, 1_956_159.44,       0.00, 12_524.54,   0.00,       0.00, 1_400_000.00, 00_000.00,     0.00, 0.00, 35_784.40 ],
    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/  co-borrower w/o +inc' => [ 'rcbc', 1_400_000, 45, 25_000, 50, 25_000, 0_000,    null, null, 0.000, 00_000.00, false, false, 14, 0.35, 17_500.0, 1_956_159.44,       0.00, 11_272.09,   0.00,  140_000.0, 1_260_000.00, 00_000.00,     0.00, 0.00, 32_205.97 ],

    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; nil dp; null mf; ₱ _0k pf no add-ons w/  co-borrower w/o +inc' => [ 'rcbc', 1_400_000, 45, 25_000, 50, 25_000, 0_000,    null, null,  null, 00_000.00, false, false, 14, 0.35, 17_500.0, 1_956_159.44,       0.00, 12_336.67,   0.00,  140_000.0, 1_379_000.00, 119_000.00,     0.00, 0.00, 35_247.63 ],
]);

test('multiple mortgage computations', function (
    string $lending_institution,
    float  $total_contract_price,
    int    $age,
    float  $monthly_gross_income,
    int    $co_borrower_age,
    float  $co_borrower_income,
    float  $additional_income,
    ?float $balance_payment_interest,
    ?float $percent_down_payment,
    ?float $percent_miscellaneous_fee,
    float  $processing_fee,
    bool   $add_mri,
    bool   $add_fi,
    int    $expected_balance_payment_term,
    float  $expected_income_requirement_multiplier,
    float  $expected_disposable_income,
    float  $expected_present_value,
    float  $expected_required_equity,
    float  $expected_monthly_amortization,
    float  $expected_add_on_fees,
    float  $expected_cash_out,
    float  $expected_loanable_amount,
    float  $expected_miscellaneous_fee,
    float  $expected_income_gap,
    float  $expected_percent_down_payment_remedy,
    float $expected_income_requirement,
) {

    $mortgage_input_data = MortgageInputsData::from(compact(
        'lending_institution',
        'total_contract_price',
        'age',
        'monthly_gross_income',
        'co_borrower_age',
        'co_borrower_income',
        'additional_income',
        'balance_payment_interest',
        'percent_down_payment',
        'percent_miscellaneous_fee',
        'processing_fee',
        'add_mri',
        'add_fi')
    );

    $mortgage_particulars = MortgageParticularsFactory::fromData($mortgage_input_data);

    // Act
    $resolved_lending_institution = ExtractorFactory::make(ExtractorType::LENDING_INSTITUTION, $mortgage_particulars)->extract();
    $resolved_interest_rate = ExtractorFactory::make(ExtractorType::INTEREST_RATE, $mortgage_particulars)->extract()->value();
    $resolved_percent_down_payment = ExtractorFactory::make(ExtractorType::PERCENT_DOWN_PAYMENT, $mortgage_particulars)->extract()->value();
    $resolved_percent_miscellaneous_fee = ExtractorFactory::make(ExtractorType::PERCENT_MISCELLANEOUS_FEES, $mortgage_particulars)->extract()->value();
    $resolved_total_contract_price = ExtractorFactory::make(ExtractorType::TOTAL_CONTRACT_PRICE, $mortgage_particulars)->toFloat();
    $resolved_income_requirement_multiplier = ExtractorFactory::make(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER, $mortgage_particulars)->extract()->value();
    $actual_balance_payment_term = CalculatorFactory::make(CalculatorType::BALANCE_PAYMENT_TERM, $mortgage_particulars)->calculate();
    $actual_income_requirement_multiplier = ExtractorFactory::make(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER, $mortgage_particulars)->extract()->value();
    $actual_disposable_income_float = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $mortgage_particulars)->calculate()->getAmount()->toFloat();
    $actual_present_value_float = CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $mortgage_particulars)->calculate()->getAmount()->toFloat();
    $actual_loanable_amount_float = CalculatorFactory::make(CalculatorType::LOAN_AMOUNT, $mortgage_particulars)->calculate()->getAmount()->toFloat();
    $actual_equity_float = CalculatorFactory::make(CalculatorType::EQUITY, $mortgage_particulars)->toFloat();
    $actual_monthly_amortization_float = CalculatorFactory::make(CalculatorType::AMORTIZATION, $mortgage_particulars)->total()->getAmount()->toFloat();
    $actual_miscellaneous_fee_float = CalculatorFactory::make(CalculatorType::MISCELLANEOUS_FEES, $mortgage_particulars)->toFloat();
    $actual_add_on_fees_float = CalculatorFactory::make(CalculatorType::FEES, $mortgage_particulars)->total()->getAmount()->toFloat();
    $actual_cash_out_float = CalculatorFactory::make(CalculatorType::CASH_OUT, $mortgage_particulars)->calculate()->total->getAmount()->toFloat();
    $actual_income_gap_float = CalculatorFactory::make(CalculatorType::INCOME_GAP, $mortgage_particulars)->toFloat();
    $actual_percent_down_payment_remedy_float = CalculatorFactory::make(CalculatorType::REQUIRED_PERCENT_DOWN_PAYMENT, $mortgage_particulars)->calculate()->value();
    $actual_income_requirement = CalculatorFactory::make(CalculatorType::INCOME_REQUIREMENT, $mortgage_particulars)->toFloat();
    $qualifies = CalculatorFactory::make(CalculatorType::LOAN_QUALIFICATION, $mortgage_particulars)->calculate();

//    dd($resolved_percent_miscellaneous_fee, ($percent_miscellaneous_fee ?? $mortgage_particulars->property()->getPercentMiscellaneousFees()?->value()) ?? $resolved_lending_institution->getPercentMiscellaneousFees()->value());
//    dd($actual_loanable_amount_float, $expected_loanable_amount);
//    dd($actual_monthly_amortization_float, $expected_monthly_amortization);
//    dd($actual_miscellaneous_fee_float, $expected_miscellaneous_fee);
//    dd($actual_income_requirement, $expected_income_requirement);

    // Assert
    expect($resolved_lending_institution->key())->toBe($lending_institution)
        ->and($resolved_interest_rate)->toBe($balance_payment_interest ?? $resolved_lending_institution->getInterestRate()->value())
        ->and($resolved_percent_down_payment)->toBe($percent_down_payment ?? $resolved_lending_institution->getPercentDownPayment()->value())
        ->and($resolved_percent_miscellaneous_fee)->toBe(($percent_miscellaneous_fee ?? $mortgage_particulars->property()->getPercentMiscellaneousFees()?->value()) ?? $resolved_lending_institution->getPercentMiscellaneousFees()->value())
        ->and($resolved_total_contract_price)->toBe($total_contract_price)
        ->and($actual_income_requirement_multiplier)->toBeCloseTo($expected_income_requirement_multiplier, 0.01)
        ->and($actual_balance_payment_term)->toBe($expected_balance_payment_term)
        ->and($actual_disposable_income_float)->toBeCloseTo($expected_disposable_income, 0.01)
        ->and($actual_present_value_float)->toBeCloseTo($expected_present_value, 0.01)
        ->and($actual_loanable_amount_float)->toBeCloseTo($expected_loanable_amount, 0.01)
        ->and($actual_equity_float)->toBeCloseTo($expected_required_equity, 0.01)
        ->and($actual_monthly_amortization_float)->toBeCloseTo($expected_monthly_amortization, 0.01)
        ->and($actual_miscellaneous_fee_float)->toBeCloseTo($expected_miscellaneous_fee, 0.01)
        ->and($actual_add_on_fees_float)->toBeCloseTo($expected_add_on_fees, 0.01)
        ->and($actual_cash_out_float)->toBeCloseTo($expected_cash_out, 0.01)
        ->and($actual_income_gap_float)->toBeCloseTo($expected_income_gap, 0.01)
        ->and($actual_percent_down_payment_remedy_float)->toBeCloseTo($expected_percent_down_payment_remedy, 0.01)
        ->and($actual_income_requirement)->toBeCloseTo($expected_income_requirement, 0.01)
        ->and($qualifies)->toBe($expected_income_gap == 0)
    ;

    $result = MortgageComputationData::fromParticulars($mortgage_particulars);

    expect($result)->toBeInstanceOf(MortgageComputationData::class)
        ->and($result->lending_institution)->toBeInstanceOf(LendingInstitution::class)
        ->and($result->lending_institution->key())->toBe($resolved_lending_institution->key())
        ->and($result->interest_rate)->toBeInstanceOf(Percent::class)
        ->and($result->interest_rate->value())->toBe($resolved_interest_rate)
        ->and($result->percent_down_payment)->toBeInstanceOf(Percent::class)
        ->and($result->percent_down_payment->value())->toBe($resolved_percent_down_payment)
        ->and($result->percent_miscellaneous_fees)->toBeInstanceOf(Percent::class)
        ->and($result->percent_miscellaneous_fees->value())->toBe($resolved_percent_miscellaneous_fee)
        ->and($result->total_contract_price)->toBeInstanceOf(Price::class)
        ->and($result->total_contract_price->inclusive()->getAmount()->toFloat())->toBe($resolved_total_contract_price)
        ->and($result->income_requirement_multiplier)->toBeInstanceOf(Percent::class)
        ->and($result->income_requirement_multiplier->value())->toBe($resolved_income_requirement_multiplier)
        ->and($result->balance_payment_term)->toBeInt()
        ->and($result->balance_payment_term)->toBe($expected_balance_payment_term)
        ->and($result->monthly_disposable_income)->toBeInstanceOf(Price::class)
        ->and($result->monthly_disposable_income->getAmount()->toFloat())->toBeCloseTo($expected_disposable_income, 0.01)
        ->and($result->present_value)->toBeInstanceOf(Price::class)
        ->and($result->present_value->getAmount()->toFloat())->toBeCloseTo($expected_present_value, 0.01)
        ->and($result->loanable_amount)->toBeInstanceOf(Price::class)
        ->and($result->loanable_amount->getAmount()->toFloat())->toBeCloseTo($expected_loanable_amount, 0.01)
        ->and($result->required_equity)->toBeInstanceOf(Price::class)
        ->and($result->required_equity->getAmount()->toFloat())->toBeCloseTo($expected_required_equity, 0.01)
        ->and($result->monthly_amortization)->toBeInstanceOf(Price::class)
        ->and($result->monthly_amortization->getAmount()->toFloat())->toBeCloseTo($expected_monthly_amortization, 0.01)
        ->and($result->miscellaneous_fees)->toBeInstanceOf(Price::class)
        ->and($result->miscellaneous_fees->getAmount()->toFloat())->toBeCloseTo($expected_miscellaneous_fee, 0.01)
        ->and($result->add_on_fees)->toBeInstanceOf(Price::class)
        ->and($result->add_on_fees->getAmount()->toFloat())->toBeCloseTo($expected_add_on_fees, 0.01)
        ->and($result->cash_out)->toBeInstanceOf(Price::class)
        ->and($result->cash_out->getAmount()->toFloat())->toBeCloseTo($expected_cash_out, 0.01)
        ->and($result->income_gap)->toBeInstanceOf(Price::class)
        ->and($result->income_gap->getAmount()->toFloat())->toBeCloseTo($expected_income_gap, 0.01)
        ->and($result->percent_down_payment_remedy)->toBeInstanceOf(Percent::class)
        ->and($result->percent_down_payment_remedy->value())->toBeCloseTo($expected_percent_down_payment_remedy, 0.01)
        ->and($result->required_income)->toBeInstanceOf(Price::class)
        ->and($result->required_income->getAmount()->toFloat())->toBeCloseTo($expected_income_requirement, 0.01)
        ->and($result->required_income)->toBeInstanceOf(Price::class)
        ->and($result->required_income->getAmount()->toFloat())->toBeCloseTo($expected_income_requirement, 0.01)
        ->and($result->inputs)->toBeInstanceOf(MortgageParticulars::class)
//        ->and($result->inputs->toArray())->toBe($inputs->toArray())
        ->and($result->qualifies)->toBe($expected_required_equity == 0)
    ;
})->with('simple amortization');

test('multiple mortgage computations - controller', function (
    string $lending_institution,
    float  $total_contract_price,
    int    $age,
    float  $monthly_gross_income,
    int    $co_borrower_age,
    float  $co_borrower_income,
    float  $additional_income,
    ?float $balance_payment_interest,
    ?float $percent_down_payment,
    ?float $percent_miscellaneous_fee,
    float  $processing_fee,
    bool   $add_mri,
    bool   $add_fi,
    int    $expected_balance_payment_term,
    float  $expected_income_requirement_multiplier,
    float  $expected_disposable_income,
    float  $expected_present_value,
    float  $expected_required_equity,
    float  $expected_monthly_amortization,
    float  $expected_add_on_fees,
    float  $expected_cash_out,
    float  $expected_loanable_amount,
    float  $expected_miscellaneous_fee,
    float  $expected_income_gap,
    float  $expected_percent_down_payment_remedy,
) {
    $mortgage_input_data = MortgageInputsData::from(compact(
            'lending_institution',
            'total_contract_price',
            'age',
            'monthly_gross_income',
            'co_borrower_age',
            'co_borrower_income',
            'additional_income',
            'balance_payment_interest',
            'percent_down_payment',
            'percent_miscellaneous_fee',
            'processing_fee',
            'add_mri',
            'add_fi')
    );
    $response = $this->postJson(route('api.v1.mortgage-compute'), $mortgage_input_data->toArray());
    $response->assertOk();

    $response->assertJson(fn ($json) =>
    $json->has('payload')
        ->where('payload.balance_payment_term', fn ($value) => $value == $expected_balance_payment_term)
        ->where('payload.income_requirement_multiplier', $expected_income_requirement_multiplier)
        ->where('payload.monthly_disposable_income', fn ($value) => $value == $expected_disposable_income) // Loose comparison
        ->where('payload.present_value', fn ($value) => $value == $expected_present_value)
        ->where('payload.required_equity', fn ($value) => $value == $expected_required_equity)
        ->where('payload.monthly_amortization', fn ($value) => $value == $expected_monthly_amortization)
        ->where('payload.add_on_fees', fn ($value) => $value == $expected_add_on_fees)
        ->where('payload.cash_out', fn ($value) => $value == $expected_cash_out)
        ->where('payload.loanable_amount', fn ($value) => $value == $expected_loanable_amount)
        ->where('payload.miscellaneous_fees', fn ($value) => $value == $expected_miscellaneous_fee)
        ->where('payload.income_gap', fn ($value) => $value == $expected_income_gap)
        ->where('payload.percent_down_payment_remedy', fn ($value) => $value == $expected_percent_down_payment_remedy)
        ->has('qualification', fn ($json) =>
        $json->where('income_gap', fn ($value) => $value == $expected_income_gap)
            ->where('required_equity', fn ($value) => $value == $expected_required_equity)
            ->where('suggested_down_payment_percent', fn ($value) => $value == $expected_percent_down_payment_remedy)
            ->where('qualifies', $expected_income_gap == 0)
            ->where('reason', $expected_income_gap == 0 ? 'Sufficient disposable income' : 'Disposable income below amortization')
        ->has('mortgage', fn ($json) =>
            $json->where('monthly_amortization', fn ($value) => $value == $expected_monthly_amortization)
                ->where('balance_payment_term', fn ($value) => $value == $expected_balance_payment_term)
            )
        )
    );
})->with('simple amortization');

test('single mortgage computation', function () {
    $age = 49;
    $gmi = 29_000;
    $tcp = 1_400_000;
    $li = 'rcbc';
    $pmf = 0;
    $interest_rate = 0.0625;

    $buyer = app(Buyer::class)
        ->setAge($age)
        ->setMonthlyGrossIncome($gmi)
    ;
    $lendingInstitution = (new LendingInstitution($li))
        ->newOffset(0)
    ;

    $property = (new Property($tcp))
        ->setLendingInstitution($lendingInstitution)
    ;

    $order = new Order;
    $order->setPercentDownPayment(0);

    if ($pmf !== null) {
        $order->setPercentMiscellaneousFees(Percent::ofFraction($pmf));
    }

    if ($interest_rate !== null) {
        $order->setInterestRate(Percent::ofFraction($interest_rate));
    }

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);

    $actual_interest_rate = ExtractorFactory::make(ExtractorType::INTEREST_RATE, $inputs)->extract()->value();
    $actual_percent_miscellaneous_fee = ExtractorFactory::make(ExtractorType::PERCENT_MISCELLANEOUS_FEES, $inputs)->extract();
    $actual_miscellaneous_fee = CalculatorFactory::make(CalculatorType::MISCELLANEOUS_FEES, $inputs)->toFloat();
    $actual_term_years = CalculatorFactory::make(CalculatorType::BALANCE_PAYMENT_TERM, $inputs)->calculate();
    $actual_amortization = CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->calculate()
        ->principal->getAmount()->toFloat();
    $actual_disposable_income = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $inputs)->toFloat();
    $actual_loan_value = CalculatorFactory::make(CalculatorType::LOAN_AMOUNT, $inputs)->toFloat();
    $actual_present_value = CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $inputs)->toFloat();
    $actual_required_equity = CalculatorFactory::make(CalculatorType::EQUITY, $inputs)->toFloat();
    $actual_cash_out = CalculatorFactory::make(CalculatorType::CASH_OUT, $inputs)->calculate()->total->getAmount()->toFloat();
    $actual_fees = CalculatorFactory::make(CalculatorType::FEES, $inputs)->toFloat();
    $actual_down_payment = CalculatorFactory::make(CalculatorType::REQUIRED_PERCENT_DOWN_PAYMENT, $inputs)->calculate();
//    dd($actual_down_payment);

    expect($actual_term_years)->toBe(16);
    expect($actual_disposable_income)->toBe(10_150.00);

    expect($actual_interest_rate)->toBe(0.0625);
    expect($actual_present_value)->toBe(1_230_013.60);

    if ($pmf === 0.0) {
        expect($actual_miscellaneous_fee)->toBe(0.0); //without mf
        expect($actual_amortization)->toBe(11_552.72); //without mf
        expect($actual_required_equity)->toBe(169_986.40); //without mf
        expect($actual_loan_value)->toBe(1_400_000.0); //without mf
    }
    elseif ($pmf === null && in_array($li,  ['rcbc', 'cbc'])) {
        expect($actual_miscellaneous_fee)->toBe(119_000.0);
        expect($actual_amortization)->toBe(12_534.70);
        expect($actual_required_equity)->toBe(288_986.40);
        expect($actual_loan_value)->toBe(1_519_000.0);
    }

    expect($actual_fees)->toBe(0.0);
    expect($actual_loan_value)->toBe($tcp + $actual_miscellaneous_fee + $actual_fees);
    expect($actual_cash_out)->toBe(0.0);
    expect($actual_percent_miscellaneous_fee->value())->toBeCloseTo($pmf);
});
