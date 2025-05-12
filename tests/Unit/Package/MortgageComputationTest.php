<?php

use LBHurtado\Mortgage\Classes\{Buyer, LendingInstitution, Order, Property};
use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType, MonthlyFee};
use LBHurtado\Mortgage\Services\{AgeService, BorrowingRulesService};
use LBHurtado\Mortgage\ValueObjects\{MiscellaneousFee, Percent};
use LBHurtado\Mortgage\Data\Payloads\MortgageResultPayload;
use LBHurtado\Mortgage\Data\QualificationResultData;
use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\Data\MortgageResultData;
use LBHurtado\Mortgage\Data\Inputs\InputsData;

beforeEach(function () {
    $this->rules = new BorrowingRulesService(new AgeService());
});

dataset('simple amortization', [
    /******************************************************************************************************************************* lender     TCP    age1  gmi1  age2   gmi2 income interest %dp   %mf      pf       MRI?   FI?  term %gmi disposable     PV        equity    amortization  fees   cash out  loanable amount   mf     income gap  %dpr ****/
    /** start  working */
    'hdmf 1.0M in 21 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 49, 17_000, 00, 17_000, 0_000, 0.0625, null,  null, 00_000.00, false, false, 21, 0.35,  5_950.0,   833_878.13, 166_121.87,  7_135.34,   0.00,       0.00, 1_000_000.00, 00_000.00, 1_185.34, 0.16 ],

    'hdmf 1.0M in 23 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 47, 21_000, 00, 21_000, 0_000, 0.0625, null, 0.000, 00_000.00, false, false, 23, 0.35,  7_350.0, 1_074_757.85,       0.00,  6_838.75,   0.00,       0.00, 1_000_000.00, 00_000.00,     0.00, 0.00 ],
    'hdmf 1.1M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_100_000, 48, 19_000, 00, 19_000, 0_000, 0.0625, null, 0.000, 00_000.00, false, false, 22, 0.35,  6_650.0,   952_820.39, 147_179.61,  7_677.21,   0.00,       0.00, 1_100_000.00, 00_000.00, 1_027.21, 0.13 ],
    'hdmf 1.2M in 23 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_200_000, 47, 21_000, 00, 21_000, 0_000, 0.0625, null, 0.000, 00_000.00, false, false, 23, 0.35,  7_350.0, 1_074_757.85, 125_242.15,  8_206.50,   0.00,       0.00, 1_200_000.00, 00_000.00,   856.50, 0.10 ],

    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0_000, 0.0625, 0.10, 0.000, 00_000.00, false, false, 22, 0.35,  6_650.0,   952_820.39,       0.00,   6281.35,   0.00, 100_000.00,   900_000.00, 00_000.00,     0.00, 0.00 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0_000, 0.0625, 0.10, 0.085, 00_000.00, false, false, 22, 0.35,  6_650.0,   952_820.39,  32_179.61,  6_874.59,   0.00, 100_000.00,   985_000.00, 85_000.00,   224.59, 0.03 ],
    /** end working */
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ _0k pf no add-ons w/  co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 50, 19_000, 0_000, 0.0625, 0.10, 0.085, 00_000.00, false, false, 20, 0.35, 13_300.0, 1_819_604.16,       0.00,  7_199.64,   0.00, 100_000.00,   985_000.00, 85_000.00,     0.00, 0.00 ],
    'hdmf 1.0M in 20 yrs @ 6.25% by a 50yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ _0k pf no add-ons w/o co-borrower w/ +inc' => [ 'hdmf', 1_000_000, 50, 19_000, 00, 19_000, 19_000, 0.0625, 0.10, 0.085, 00_000.00, false, false, 20, 0.35, 13_300.0, 1_819_604.16,      0.00,  7_199.64,   0.00, 100_000.00,    985_000.00, 85_000.00,    0.00,  0.00 ],

    /** start working */
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ 10k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0_000, 0.0625, 0.10, 0.085, 10_000.00, false, false, 22, 0.35,  6_650.0,   952_820.39,  32_179.61,  6_874.59,   0.00, 110_000.00,   985_000.00, 85_000.00,   224.59, 0.03 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ 10k pf mri and fi w/o co-borrower w/o +inc' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0_000, 0.0625, 0.10, 0.085, 10_000.00,  true,  true, 22, 0.35,  6_650.0,   952_820.39,  32_179.61,  7_276.74, 402.15, 110_000.00,   985_000.00, 85_000.00,   626.74, 0.03 ],
    'hdmf 1.3M in 24 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_300_000, 46, 23_000, 00, 23_000, 0_000, 0.0625, null, 0.000, 00_000.00, false, false, 24, 0.35,  8_050.0, 1_199_384.92, 100_615.08,  8_725.31,   0.00,       0.00, 1_300_000.00, 00_000.00,   675.31, 0.07 ],
    'hdmf 1.4M in 25 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; nil dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'hdmf', 1_400_000, 45, 25_000, 00, 25_000, 0_000, 0.0625, null, 0.000, 00_000.00, false, false, 25, 0.35,  8_750.0, 1_326_422.04,  73_577.96,  9_235.37,   0.00,       0.00, 1_400_000.00, 00_000.00,   485.37, 0.05 ],
    /** end working */
    'rcbc 1.0M in 15 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_000_000, 49, 17_000, 00, 17_000, 0_000, 0.0625, 0.00, 0.000, 00_000.00, false, false, 15, 0.35,  5_950.0,   693_939.97, 306_060.03,  8_574.23,   0.00,       0.00, 1_000_000.00, 00_000.00, 2_624.23, 0.30 ],
    'rcbc 1.1M in 16 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_100_000, 48, 19_000, 00, 19_000, 0_000, 0.0625, 0.00, 0.000, 00_000.00, false, false, 16, 0.35,  6_650.0,   805_870.98, 294_129.02,  9_077.14,   0.00,       0.00, 1_100_000.00, 00_000.00, 2_427.14, 0.26 ],
    'rcbc 1.2M in 17 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_200_000, 47, 21_000, 00, 21_000, 0_000, 0.0625, 0.00, 0.000, 00_000.00, false, false, 17, 0.35,  7_350.0,   922_155.72, 277_844.28,  9_564.55,   0.00,       0.00, 1_200_000.00, 00_000.00, 2_214.55, 0.23 ],
    'rcbc 1.3M in 18 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_300_000, 46, 23_000, 00, 23_000, 0_000, 0.0625, 0.00, 0.000, 00_000.00, false, false, 18, 0.35,  8_050.0, 1_042_350.02, 257_649.98, 10_039.81,   0.00,       0.00, 1_300_000.00, 00_000.00, 1_989.81, 0.19 ],
    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower w/o +inc' => [ 'rcbc', 1_400_000, 45, 25_000, 00, 25_000, 0_000, 0.0625, 0.00, 0.000, 00_000.00, false, false, 19, 0.35,  8_750.0, 1_166_047.51, 233_952.49, 10_505.58,   0.00,       0.00, 1_400_000.00, 00_000.00, 1_755.58, 0.16 ],
    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/  co-borrower w/o +inc' => [ 'rcbc', 1_400_000, 45, 25_000, 50, 25_000, 0_000, 0.0625, 0.00, 0.000, 00_000.00, false, false, 14, 0.35, 17_500.0, 1_956_159.44,       0.00, 12_524.54,   0.00,       0.00, 1_400_000.00, 00_000.00,     0.00, 0.00 ],
]);

test('mortgage computations', function (
    string $lending_institution,
    float  $total_contract_price,
    int    $age,
    float  $monthly_gross_income,
    int    $co_borrower_age,
    float  $co_borrower_income,
    float  $additional_income,
    float  $balance_payment_interest,
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
    // Arrange
    $buyer = app(Buyer::class)
        ->setAge($age)
        ->setMonthlyGrossIncome($monthly_gross_income)
        ->addOtherSourcesOfIncome('test', $additional_income)
    ;

    expect($buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat())->toBe($monthly_gross_income + $additional_income);

    if ($co_borrower_age) {
        $co_borrower = app(Buyer::class)
            ->setAge($co_borrower_age)
            ->setMonthlyGrossIncome($co_borrower_income)
        ;
        $buyer->addCoBorrower($co_borrower);
    }

    $property = (new Property($total_contract_price))
        ->setLendingInstitution(new LendingInstitution($lending_institution))
    ;

    $order = (new Order())
        ->setLendingInstitution($property->getLendingInstitution()) //TODO: refactor this, decouple lending institution from order - used in Monthly Fee
    ;

    if ($percent_miscellaneous_fee) {
        $order->setPercentMiscellaneousFees(Percent::ofFraction($percent_miscellaneous_fee));
    }
    if ($processing_fee) {
        $order->setProcessingFee($processing_fee);
    }
    if ($add_mri) {
        $order->addMonthlyFee(MonthlyFee::MRI);
    }
    if ($add_fi) {
        $order->addMonthlyFee(MonthlyFee::FIRE_INSURANCE);
    }
    if (!is_null($percent_down_payment)) {
        $order->setPercentDownPayment($percent_down_payment);
    }

    $inputs = InputsData::fromBooking($buyer, $property, $order);

    // Act
    $actual_term_years = CalculatorFactory::make(CalculatorType::BALANCE_PAYMENT_TERM, $inputs)->calculate();
    $actual_income_requirement_multiplier = ExtractorFactory::make(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER, $inputs)->extract()->value();
    $actual_disposable_income_float = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $inputs)->calculate()->getAmount()->toFloat();
    $actual_monthly_amortization_float = CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->total()->getAmount()->toFloat();
    $actual_present_value_float = CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $inputs)->calculate()->getAmount()->toFloat();
    $actual_equity_float = CalculatorFactory::make(CalculatorType::EQUITY, $inputs)->calculate()->amount->getAmount()->toFloat();
    $actual_cash_out = CalculatorFactory::make(CalculatorType::CASH_OUT, $inputs)->calculate()->total->getAmount()->toFloat();
    $actual_loanable_amount = CalculatorFactory::make(CalculatorType::LOANABLE_AMOUNT, $inputs)->calculate()->getAmount()->toFloat();
    $actual_add_on_fees = CalculatorFactory::make(CalculatorType::FEES, $inputs)->total()->getAmount()->toFloat();
    $actual_miscellaneous_fee = MiscellaneousFee::fromInputs($inputs)->total()->getAmount()->toFloat();

    // Assert
    expect($buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat())->toBe($monthly_gross_income + $additional_income)
        ->and($actual_term_years)->toBe($expected_balance_payment_term)
        ->and($actual_income_requirement_multiplier)->toBeCloseTo($expected_income_requirement_multiplier, 0.01)
        ->and($actual_disposable_income_float)->toBeCloseTo($expected_disposable_income, 0.01)
        ->and($actual_present_value_float)->toBeCloseTo($expected_present_value, 0.01)
        ->and($actual_equity_float)->toBeCloseTo($expected_required_equity, 0.01)
        ->and($actual_monthly_amortization_float)->toBeCloseTo($expected_monthly_amortization, 0.01)
        ->and($actual_cash_out)->toBeCloseTo($expected_cash_out, 0.01)
        ->and($actual_loanable_amount)->toBeCloseTo($expected_loanable_amount, 0.01)
        ->and($actual_add_on_fees)->toBeCloseTo($expected_add_on_fees, 0.01)
        ->and($actual_miscellaneous_fee)->toBeCloseTo($expected_miscellaneous_fee, 0.01)
    ;

    $result = MortgageResultData::fromInputs($inputs);
    expect($result)->toBeInstanceOf(MortgageResultData::class)
        ->and($result->inputs->toArray())->toBe($inputs->toArray())
        ->and($result->term_years)->toBe($expected_balance_payment_term)
        ->and($result->monthly_disposable_income->getAmount()->toFloat())->toBeCloseTo($expected_disposable_income, 0.01)
        ->and($result->present_value->getAmount()->toFloat())->toBeCloseTo($expected_present_value, 0.01)
        ->and($result->required_equity->getAmount()->toFloat())->toBeCloseTo($expected_required_equity, 0.01)
        ->and($result->monthly_amortization->getAmount()->toFloat())->toBeCloseTo($expected_monthly_amortization, 0.01)
        ->and($result->add_on_fees->getAmount()->toFloat())->toBeCloseTo($expected_add_on_fees, 0.01)
        ->and($result->cash_out->getAmount()->toFloat())->toBeCloseTo($expected_cash_out, 0.01)
        ->and($result->loanable_amount->getAmount()->toFloat())->toBeCloseTo($expected_loanable_amount, 0.01)
        ->and($result->miscellaneous_fee->getAmount()->toFloat())->toBeCloseTo($expected_miscellaneous_fee, 0.01)
    ;

    $payload = MortgageResultPayload::fromResult($result);
    expect($payload)->toBeInstanceOf(MortgageResultPayload::class)
        ->and($payload->inputs->gross_monthly_income)->toBeCloseTo($monthly_gross_income + $additional_income, 0.01)
        ->and($payload->inputs->income_requirement_multiplier)->toBeCloseTo($expected_income_requirement_multiplier, 0.01)
        ->and($payload->inputs->total_contract_price)->toBeCloseTo($total_contract_price, 0.01)
        ->and($payload->inputs->percent_down_payment)->toBeCloseTo($percent_down_payment, 0.01)
//        ->and($payload->inputs->down_payment_term)->toBeCloseTo(12, 0.01)
        ->and($payload->inputs->percent_loanable)->toBeCloseTo(0.95, 0.01)//TODO: unfix this
        ->and($payload->inputs->appraisal_value)->toBeCloseTo($total_contract_price, 0.01)//TODO: unfix this
        ->and($payload->inputs->discount_amount)->toBeNull()//TODO: unfix this
        ->and($payload->inputs->low_cash_out)->toBeNull()//TODO: unfix this
        ->and($payload->inputs->waived_processing_fee)->toBeNull()//TODO: unfix this
//        ->and($payload->inputs->balance_payment_term)->toBe($expected_balance_payment_term)
        ->and($payload->term_years)->toBe($expected_balance_payment_term)
//        ->and($payload->inputs->balance_payment_interest_rate)->toBe($balance_payment_interest)//TODO: check this out
//        ->and($payload->inputs->percent_miscellaneous_fee)->toBe($percent_miscellaneous_fee)//TODO: check this out
        ->and($payload->inputs->consulting_fee)->toBeNull()
        ->and($payload->inputs->processing_fee)->toBeCloseTo($processing_fee, 0.01)
//        ->and($payload->inputs->monthly_mri)->toBe(0.0 )
//        ->and($payload->inputs->monthly_fi)->toBe(0.0 )
        ->and($payload->term_years)->toBe($expected_balance_payment_term)//TODO: redundant?
        ->and($payload->monthly_disposable_income)->toBeCloseTo($expected_disposable_income, 0.01)
        ->and($payload->present_value)->toBeCloseTo($expected_present_value, 0.01)
        ->and($payload->required_equity)->toBeCloseTo($expected_required_equity, 0.01)
        ->and($payload->monthly_amortization)->toBeCloseTo($expected_monthly_amortization, 0.01)
        ->and($payload->add_on_fees)->toBeCloseTo($expected_add_on_fees, 0.01)
        ->and($payload->cash_out)->toBeCloseTo($expected_cash_out, 0.01)
        ->and($payload->loanable_amount)->toBeCloseTo($expected_loanable_amount, 0.01)
        ->and($payload->miscellaneous_fee)->toBeCloseTo($expected_miscellaneous_fee, 0.01)
    ;

    $result = QualificationResultData::fromInputs($inputs);
    expect($result)->toBeInstanceOf(QualificationResultData::class)
        ->and($result->mortgage)->toBeInstanceOf(MortgageResultData::class)
        ->and($result->loan_difference->inclusive()->getAmount()->toFloat())->toBeCloseTo($expected_required_equity)
        ->and($result->income_gap->inclusive()->getAmount()->toFloat())->toBeCloseTo($expected_income_gap)
        ->and($result->suggested_down_payment_percent->value())->toBeCloseTo($expected_percent_down_payment_remedy)
        ->and(in_array($result->reason, [
            'Sufficient disposable income',
            'Disposable income below amortization',
        ]))->toBeTrue()
    ;

})->with('simple amortization');
