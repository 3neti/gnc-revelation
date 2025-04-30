<?php

use LBHurtado\Mortgage\Classes\{Buyer, LendingInstitution, Order, Property};
use LBHurtado\Mortgage\Services\{AgeService, BorrowingRulesService};
use LBHurtado\Mortgage\ValueObjects\{MiscellaneousFee, Percent};
use LBHurtado\Mortgage\Enums\{CalculatorType, MonthlyFee};
use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Data\Inputs\InputsData;

beforeEach(function () {
    $this->rules = new BorrowingRulesService(new AgeService());
});

//TODO: additional income
dataset('simple amortization', [
    /********************************************************************************************************************** lender     TCP    age1  gmi1  age2   gmi2  %gmi interest  %dp   %mf      pf       MRI?   FI?  term disposable     PV        equity    amortization  fees   cash out  loanable amount   mf ****/
    'hdmf 1.0M in 21 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_000_000, 49, 17_000, 00, 17_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 21,  5_950.0,   833_878.13, 166_121.87,  7_135.34,   0.00,       0.00, 1_000_000.00, 00_000.00 ],
    'hdmf 1.0M in 23 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_000_000, 47, 21_000, 00, 21_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 23,  7_350.0, 1_074_757.85,       0.00,  6_838.75,   0.00,       0.00, 1_000_000.00, 00_000.00 ],
    'hdmf 1.1M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_100_000, 48, 19_000, 00, 19_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 22,  6_650.0,   952_820.39, 147_179.61,  7_677.21,   0.00,       0.00, 1_100_000.00, 00_000.00 ],
    'hdmf 1.2M in 23 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_200_000, 47, 21_000, 00, 21_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 23,  7_350.0, 1_074_757.85, 125_242.15,  8_206.50,   0.00,       0.00, 1_200_000.00, 00_000.00 ],

    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0.35, 0.0625, 0.10, 0.000, 00_000.00, false, false, 22,  6_650.0,   952_820.39,       0.00,   6281.35,   0.00, 100_000.00,   900_000.00, 00_000.00 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0.35, 0.0625, 0.10, 0.085, 00_000.00, false, false, 22,  6_650.0,   952_820.39,  32_179.61,  6_874.59,   0.00, 100_000.00,   985_000.00, 85_000.00 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ _0k pf no add-ons w/  co-borrower' => [ 'hdmf', 1_000_000, 48, 19_000, 50, 19_000, 0.35, 0.0625, 0.10, 0.085, 00_000.00, false, false, 20, 13_300.0, 1_819_604.16,       0.00,  7_199.64,   0.00, 100_000.00,   985_000.00, 85_000.00 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ 10k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0.35, 0.0625, 0.10, 0.085, 10_000.00, false, false, 22,  6_650.0,   952_820.39,  32_179.61,  6_874.59,   0.00, 110_000.00,   985_000.00, 85_000.00 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ 10k pf mri and fi w/o co-borrower' => [ 'hdmf', 1_000_000, 48, 19_000, 00, 19_000, 0.35, 0.0625, 0.10, 0.085, 10_000.00,  true,  true, 22,  6_650.0,   952_820.39,  32_179.61,  7_276.74, 402.15, 110_000.00,   985_000.00, 85_000.00 ],

    'hdmf 1.3M in 24 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_300_000, 46, 23_000, 00, 23_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 24,  8_050.0, 1_199_384.92, 100_615.08,  8_725.31,   0.00,       0.00, 1_300_000.00, 00_000.00 ],
    'hdmf 1.4M in 25 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'hdmf', 1_400_000, 45, 25_000, 00, 25_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 25,  8_750.0, 1_326_422.04,  73_577.96,  9_235.37,   0.00,       0.00, 1_400_000.00, 00_000.00 ],

    'rcbc 1.0M in 15 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'rcbc', 1_000_000, 49, 17_000, 00, 17_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 15,  5_950.0,   693_939.97, 306_060.03,  8_574.23,   0.00,       0.00, 1_000_000.00, 00_000.00 ],
    'rcbc 1.1M in 16 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'rcbc', 1_100_000, 48, 19_000, 00, 19_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 16,  6_650.0,   805_870.98, 294_129.02,  9_077.14,   0.00,       0.00, 1_100_000.00, 00_000.00 ],
    'rcbc 1.2M in 17 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'rcbc', 1_200_000, 47, 21_000, 00, 21_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 17,  7_350.0,   922_155.72, 277_844.28,  9_564.55,   0.00,       0.00, 1_200_000.00, 00_000.00 ],
    'rcbc 1.3M in 18 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'rcbc', 1_300_000, 46, 23_000, 00, 23_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 18,  8_050.0, 1_042_350.02, 257_649.98, 10_039.81,   0.00,       0.00, 1_300_000.00, 00_000.00 ],
    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/o co-borrower' => [ 'rcbc', 1_400_000, 45, 25_000, 00, 25_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 19,  8_750.0, 1_166_047.51, 233_952.49, 10_505.58,   0.00,       0.00, 1_400_000.00, 00_000.00 ],
    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi; _0% dp; 0.0% mf; ₱ _0k pf no add-ons w/  co-borrower' => [ 'rcbc', 1_400_000, 45, 25_000, 50, 25_000, 0.35, 0.0625, 0.00, 0.000, 00_000.00, false, false, 14, 17_500.0, 1_956_159.44,       0.00, 12_524.54,   0.00,       0.00, 1_400_000.00, 00_000.00 ],
]);

test('mortgage computations', function (
    string $lending_institution,
    float  $total_contract_price,
    int    $age,
    float  $monthly_gross_income,
    int    $co_borrower_age,
    float  $co_borrower_income,
    float  $income_requirement_multiplier,
    float  $balance_payment_interest,
    float  $percent_down_payment,
    float  $percent_miscellaneous_fee,
    float  $processing_fee,
    bool   $add_mri,
    bool   $add_fi,
    int    $expected_balance_payment_term,
    float  $expected_disposable_income,
    float  $expected_present_value,
    float  $expected_required_equity,
    float  $expected_monthly_amortization,
    float  $expected_add_on_fees,
    float  $expected_cash_out,
    float  $expected_loanable_amount,
    float  $expected_miscellaneous_fee,
) {
    // Arrange
    $buyer = app(Buyer::class)
        ->setAge($age)
        ->setMonthlyGrossIncome($monthly_gross_income)
        ->setLendingInstitution(new LendingInstitution($lending_institution))
        ->setIncomeRequirementMultiplier($income_requirement_multiplier)//remove this later
    ;
    expect($buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat())->toBe($monthly_gross_income);

    if ($co_borrower_age) {
        $co_borrower = app(Buyer::class)
            ->setAge($co_borrower_age)
            ->setMonthlyGrossIncome($co_borrower_income)
            ->setLendingInstitution(new LendingInstitution($lending_institution))
            ->setIncomeRequirementMultiplier($income_requirement_multiplier)
        ;
        $buyer
            ->setIncomeRequirementMultiplier($income_requirement_multiplier)
            ->addCoBorrower($co_borrower);

        $buyer->setIncomeRequirementMultiplier($income_requirement_multiplier);
    }

    $property = (new Property($total_contract_price));
    $order = (new Order())
        ->setInterestRate(Percent::ofFraction($balance_payment_interest))
        ->setIncomeRequirementMultiplier(Percent::ofFraction($income_requirement_multiplier))
        ->setPercentMiscellaneousFees(Percent::ofFraction($percent_miscellaneous_fee))
        ->setProcessingFee($processing_fee)
        ->setLendingInstitution(new LendingInstitution($lending_institution))
        ->setTotalContractPrice($total_contract_price)
    ;
    if ($add_mri) {
        $order->addMonthlyFee(MonthlyFee::MRI);
    }
    if ($add_fi) {
        $order->addMonthlyFee(MonthlyFee::FIRE_INSURANCE);
    }
    if ($percent_down_payment) {
        $order->setPercentDownPayment($percent_down_payment);
    }
    $inputs = InputsData::fromBooking($buyer, $property, $order);

    // Act
    $actual_term_years = $buyer->getJointMaximumTermAllowed();
    $actual_disposable_income_float = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $inputs)->calculate()->getAmount()->toFloat();
    $actual_monthly_amortization_float = CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->total()->getAmount()->toFloat();
    $actual_present_value_float = CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $inputs)->calculate()->getAmount()->toFloat();
    $actual_equity_float = CalculatorFactory::make(CalculatorType::EQUITY, $inputs)->calculate()->amount->getAmount()->toFloat();
    $actual_cash_out = CalculatorFactory::make(CalculatorType::CASH_OUT, $inputs)->calculate()->total->getAmount()->toFloat();
    $actual_loanable_amount = CalculatorFactory::make(CalculatorType::LOANABLE_AMOUNT, $inputs)->calculate()->getAmount()->toFloat();
    $actual_add_on_fees = CalculatorFactory::make(CalculatorType::FEES, $inputs)->total()->getAmount()->toFloat();
    $actual_miscellaneous_fee = MiscellaneousFee::fromInputs($inputs)->total()->getAmount()->toFloat();
//    dd($actual_miscellaneous_fee, $expected_miscellaneous_fee);
//    dd($inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat());
//    dd($actual_loanable_amount, $expected_loanable_amount);
//    dd($actual_equity_float, $expected_required_equity );

//    dd(
//        CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->total()->getAmount()->toFloat(),
//        CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->monthlyMiscFee()->getAmount()->toFloat(),
//        CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->addOns()->getAmount()->toFloat()
//    );
//    dd($actual_monthly_amortization_float, $expected_monthly_amortization, $actual_monthly_amortization_float - $expected_monthly_amortization);
//    dd($actual_cash_out, $expected_cash_out);

//    dd($actual_disposable_income_float, $expected_disposable_income);
//    dd($actual_present_value_float, $expected_present_value);
//    dd($actual_equity_float, $expected_required_equity);
//    dd($actual_monthly_amortization_float, $expected_monthly_amortization);
    // Assert
    expect($buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat())->toBe($monthly_gross_income)
        ->and($actual_term_years)->toBe($expected_balance_payment_term)
        ->and($actual_disposable_income_float)->toBeCloseTo($expected_disposable_income, 0.01)
        ->and($actual_present_value_float)->toBeCloseTo($expected_present_value, 0.01)
        ->and($actual_equity_float)->toBeCloseTo($expected_required_equity, 0.01)
        ->and($actual_monthly_amortization_float)->toBeCloseTo($expected_monthly_amortization, 0.01)
        ->and($actual_cash_out)->toBeCloseTo($expected_cash_out, 0.01)
        ->and($actual_loanable_amount)->toBeCloseTo($expected_loanable_amount, 0.01)
        ->and($actual_add_on_fees)->toBeCloseTo($expected_add_on_fees, 0.01)
        ->and($actual_miscellaneous_fee)->toBeCloseTo($expected_miscellaneous_fee, 0.01)
    ;

})->with('simple amortization');

//it('computes monthly amortization accurately', function () {
//    $buyer = (new Buyer($this->rules))
//        ->setMonthlyGrossIncome(17_000)
//        ->setIncomeRequirementMultiplier(0.35);
//
//    $property = new Property(1_000_000);
//
//    $order = (new Order())
//        ->setInterestRate(Percent::ofPercent(6.25))
////        ->setPercentDownPayment(0.10)
//        ->setIncomeRequirementMultiplier(0.35)
//        ->setDownPaymentTerm(12)
//        ->setBalancePaymentTerm(21)
//        ->setMonthlyFee(MonthlyFee::MRI, 0)
//        ->setMonthlyFee(MonthlyFee::FIRE_INSURANCE, 0);
//
////    $order->setPercentMiscellaneousFees(Percent::ofPercent(3));
//
//    $inputs = InputsData::fromBooking($buyer, $property, $order);
//    $service = new MortgageComputation($inputs);
//    dump([
//        'tcp' => $property->getTotalContractPrice()->inclusive()->getAmount()->toFloat(),
//        'down_payment_percent' => $order->getPercentDownPayment()?->value(),
//        'interest_rate_percent' => $order->getInterestRate()?->value(),
//        'monthly_add_ons' => [
//            'MRI' => $order->getMonthlyFee(MonthlyFee::MRI),
//            'Fire' => $order->getMonthlyFee(MonthlyFee::FIRE_INSURANCE),
//        ],
//        'balance_term_years' => $order->getBalancePaymentTerm(),
//        'income_requirement_multiplier' => $order->getIncomeRequirementMultiplier()?->value(),
//        'gross_income' => $buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat(),
//    ]);
//    $monthly = $service->getMonthlyAmortization();
//    $amount = $monthly->inclusive()->getAmount()->toFloat();
//
//    expect($monthly)->toBeInstanceOf(Price::class)
////        ->and($amount)->toBeCloseTo(6421.80, 0.01)
//        ->and($amount)->toBeCloseTo(7135.34, 0.01)
//    ;
//});

//it('computes present value from disposable income', function () {
//    $buyer = (new Buyer($this->rules))
//        ->setMonthlyGrossIncome(17_000)
//        ->setIncomeRequirementMultiplier(0.35);
//
//    $property = new Property(1_000_000);
//
//    $order = (new Order())
//        ->setInterestRate(0.0625)
//        ->setPercentDownPayment(0.10)
//        ->setIncomeRequirementMultiplier(0.35)
//        ->setBalancePaymentTerm(21);
//
//    $inputs = InputsData::fromBooking($buyer, $property, $order);
//    $service = new MortgageComputation($inputs);
//
//    expect($service->getPresentValueFromDisposable()->inclusive()->getAmount()->toFloat())
//        ->toBeCloseTo(833878.13, 0.01);
//});
//
//it('computes required equity if TCP is greater than loanable', function () {
//    $tcp = 833_878.13 * 1.10; // 10% above max
//
//    $buyer = (new Buyer($this->rules))
//        ->setMonthlyGrossIncome(17_000)
//        ->setIncomeRequirementMultiplier(0.35);
//
//    $property = new Property($tcp);
//
//    $order = (new Order())
//        ->setInterestRate(0.0625)
//        ->setPercentDownPayment(0.10)
//        ->setIncomeRequirementMultiplier(0.35)
//        ->setBalancePaymentTerm(21);
//
//    $inputs = InputsData::fromBooking($buyer, $property, $order);
//    $service = new MortgageComputation($inputs);
//
//    $equity = $service->computeRequiredEquity();
//    $expected = $tcp - 833_878.13;
//
//    expect($equity)->toBeInstanceOf(Equity::class)
//        ->and($equity->toPrice()->inclusive()->getAmount()->toFloat())
//        ->toBeCloseTo($expected, 0.01);
//});
//
//it('includes add-ons in monthly amortization breakdown', function () {
//    $buyer = (new Buyer($this->rules))
//        ->setMonthlyGrossIncome(17_000)
//        ->setIncomeRequirementMultiplier(0.35);
//
//    $property = new Property(1_000_000);
//
//    $order = (new Order())
//        ->setInterestRate(0.0625)
//        ->setPercentDownPayment(0.10)
//        ->setIncomeRequirementMultiplier(0.35)
//        ->setDownPaymentTerm(12)
//        ->setBalancePaymentTerm(21)
//        ->addMonthlyFee(MonthlyFee::MRI, 150.0)
//        ->addMonthlyFee(MonthlyFee::FIRE_INSURANCE, 75.0);
//
//    $inputs = InputsData::fromBooking($buyer, $property, $order);
//    $service = new MortgageComputation($inputs);
//
//    $breakdown = $service->getMonthlyAmortizationBreakdown();
//
//    expect($breakdown->add_ons->inclusive()->getAmount()->toFloat())
//        ->toBeCloseTo(225.0, 0.01);
//});
