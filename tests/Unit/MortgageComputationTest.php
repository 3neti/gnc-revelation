<?php

use App\Classes\{Buyer, Property, Order};
use App\Services\BorrowingRulesService;
use App\Factories\CalculatorFactory;
use App\Classes\LendingInstitution;
use App\Data\Inputs\InputsData;
use App\ValueObjects\Percent;
use App\Enums\CalculatorType;
use App\Services\AgeService;

beforeEach(function () {
    $this->rules = new BorrowingRulesService(new AgeService());
});

dataset('simple amortization', [
//    'hdmf 1.0M in 21 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'hdmf', 1_000_000, 49, 17_000, 0.35, 0.0625, 0.00, 0.000, 21, 5_950.0,   833_878.13, 166_121.87,  7_135.34,   0_000.00, 1_000_000.00 ],
//    'hdmf 1.0M in 23 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'hdmf', 1_000_000, 47, 21_000, 0.35, 0.0625, 0.00, 0.000, 23, 7_350.0, 1_074_757.85,       0.00,  6_838.75,   0_000.00, 1_000_000.00 ],
//    'hdmf 1.1M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'hdmf', 1_100_000, 48, 19_000, 0.35, 0.0625, 0.00, 0.000, 22, 6_650.0,   952_820.39, 147_179.61,  7_677.21,   0_000.00, 1_100_000.00 ],
//    'hdmf 1.2M in 23 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'hdmf', 1_200_000, 47, 21_000, 0.35, 0.0625, 0.00, 0.000, 23, 7_350.0, 1_074_757.85, 125_242.15,  8_206.50,   0_000.00, 1_200_000.00 ],

//    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 0.0% mf; ₱ 0k pf' => [ 'hdmf', 1_000_000, 48, 19_000, 0.35, 0.0625, 0.10, 0.000, 22, 6_650.0,   952_820.39,       0.00,   6281.35,  100_000.00,  900_000.00 ],
    'hdmf 1.0M in 22 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi; 10% dp; 8.5% mf; ₱ 0k pf' => [ 'hdmf', 1_000_000, 48, 19_000, 0.35, 0.0625, 0.10, 0.085, 22, 6_650.0,   952_820.39,  32_179.61,  6_874.59,  100_000.00,  985_000.00 ],

//    'hdmf 1.3M in 24 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'hdmf', 1_300_000, 46, 23_000, 0.35, 0.0625, 0.00, 0.000, 24, 8_050.0, 1_199_384.92, 100_615.08,  8_725.31,   0_000.00, 1_300_000.00 ],
//    'hdmf 1.4M in 25 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'hdmf', 1_400_000, 45, 25_000, 0.35, 0.0625, 0.00, 0.000, 25, 8_750.0, 1_326_422.04,  73_577.96,  9_235.37,   0_000.00, 1_400_000.00 ],
//    'rcbc 1.0M in 15 yrs @ 6.25% by a 49yo w/ [35%] ₱17,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'rcbc', 1_000_000, 49, 17_000, 0.35, 0.0625, 0.00, 0.000, 15, 5_950.0,   693_939.97, 306_060.03,  8_574.23,   0_000.00, 1_000_000.00 ],
//    'rcbc 1.1M in 16 yrs @ 6.25% by a 48yo w/ [35%] ₱19,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'rcbc', 1_100_000, 48, 19_000, 0.35, 0.0625, 0.00, 0.000, 16, 6_650.0,   805_870.98, 294_129.02,  9_077.14,   0_000.00, 1_100_000.00 ],
//    'rcbc 1.2M in 17 yrs @ 6.25% by a 47yo w/ [35%] ₱21,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'rcbc', 1_200_000, 47, 21_000, 0.35, 0.0625, 0.00, 0.000, 17, 7_350.0,   922_155.72, 277_844.28,  9_564.55,   0_000.00, 1_200_000.00 ],
//    'rcbc 1.3M in 18 yrs @ 6.25% by a 46yo w/ [35%] ₱23,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'rcbc', 1_300_000, 46, 23_000, 0.35, 0.0625, 0.00, 0.000, 18, 8_050.0, 1_042_350.02, 257_649.98, 10_039.81,   0_000.00, 1_300_000.00 ],
//    'rcbc 1.4M in 19 yrs @ 6.25% by a 45yo w/ [35%] ₱25,000 gmi;  0% dp; 0.0% mf; ₱ 0k pf' => [ 'rcbc', 1_400_000, 45, 25_000, 0.35, 0.0625, 0.00, 0.000, 19, 8_750.0, 1_166_047.51, 233_952.49, 10_505.58,   0_000.00, 1_400_000.00 ],
]);

it('mortgage computations', function (
    string $lending_institution,
    float  $total_contract_price,
    int    $age,
    float  $monthly_gross_income,
    float  $income_requirement_multiplier,
    float  $balance_payment_interest,
    float  $percent_down_payment,
    float  $percent_miscellaneous_fee,
    int    $expected_balance_payment_term,
    float  $expected_disposable_income,
    float  $expected_present_value,
    float  $expected_required_equity,
    float  $expected_monthly_amortization,
    float  $expected_cash_out,
    float  $expected_loanable_amount
) {
    // Arrange
    $buyer = app(Buyer::class)
        ->setAge($age)
        ->setMonthlyGrossIncome($monthly_gross_income)
        ->setLendingInstitution(new LendingInstitution($lending_institution));
    expect($buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat())->toBe($monthly_gross_income);

    $property = (new Property($total_contract_price));
    $order = (new Order())
        ->setInterestRate(Percent::ofFraction($balance_payment_interest))
        ->setIncomeRequirementMultiplier(Percent::ofFraction($income_requirement_multiplier))
        ->setPercentMiscellaneousFees(Percent::ofFraction($percent_miscellaneous_fee))
    ;
    if ($percent_down_payment) {
        $order->setPercentDownPayment($percent_down_payment);
    }
    $inputs = InputsData::fromBooking($buyer, $property, $order);

    // Act
    $actual_term_years = $buyer->getMaximumTermAllowed();
    $actual_disposable_income_float = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $inputs)->calculate()->getAmount()->toFloat();
    $actual_monthly_amortization_float = CalculatorFactory::make(CalculatorType::AMORTIZATION, $inputs)->calculate()->principal->getAmount()->toFloat();
    $actual_present_value_float = CalculatorFactory::make(CalculatorType::PRESENT_VALUE, $inputs)->calculate()->getAmount()->toFloat();
    $actual_equity_float = CalculatorFactory::make(CalculatorType::EQUITY, $inputs)->calculate()->amount->getAmount()->toFloat();
    $actual_cash_out = CalculatorFactory::make(CalculatorType::CASH_OUT, $inputs)->calculate()->total->getAmount()->toFloat();
    $actual_loanable_amount = CalculatorFactory::make(CalculatorType::LOANABLE_AMOUNT, $inputs)->calculate()->getAmount()->toFloat();

//    dd($inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat());
//    dd($actual_loanable_amount, $expected_loanable_amount);
//    dd($actual_equity_float, $expected_required_equity );
//    dd($actual_monthly_amortization_float, $expected_monthly_amortization, $actual_monthly_amortization_float - $expected_monthly_amortization);
//    dd($actual_cash_out, $expected_cash_out);

    // Assert
    expect($buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat())->toBe($monthly_gross_income)
        ->and($actual_term_years)->toBe($expected_balance_payment_term)
        ->and($actual_disposable_income_float)->toBeCloseTo($expected_disposable_income, 0.01)
        ->and($actual_present_value_float)->toBeCloseTo($expected_present_value, 0.01)
        ->and($actual_equity_float)->toBeCloseTo($expected_required_equity, 0.01)
        ->and($actual_monthly_amortization_float)->toBeCloseTo($expected_monthly_amortization, 0.01)
        ->and($actual_cash_out)->toBeCloseTo($expected_cash_out, 0.01)
        ->and($actual_loanable_amount)->toBeCloseTo($expected_loanable_amount, 0.01)
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
