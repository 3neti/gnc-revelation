<?php

use App\Services\MortgageComputation;
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\ValueObjects\Equity;
use Tests\Fakes\FlexibleFakeBuyer;
use Tests\Fakes\FlexibleFakeOrder;
use Tests\Fakes\FlexibleFakeProperty;
use Whitecube\Price\Price;

it('computes monthly amortization accurately', function () {
    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: 17_000,
        joint_maximum_term_allowed: 21
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: 1_000_000
    );

    $order = new FlexibleFakeOrder(
        interest: 0.0625,
        income_requirement_multiplier: 0.35,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);
    $monthly = $service->getMonthlyAmortization();
    $amount = $monthly->inclusive()->getAmount()->toFloat();

    expect($monthly)->toBeInstanceOf(Price::class)
        ->and($amount)->toBeCloseTo(7135.34, 0.01);
});


dataset('monthly amortization scenarios', function () {
    return [
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 21' => [1000000, 17000, 0.35, 0.0625, 21,  7_135.34,  0.80],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 22' => [1000000, 17000, 0.35, 0.0625, 22,  6_979.28,  0.01],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 23' => [1000000, 17000, 0.35, 0.0625, 23,  6_838.75,  0.01],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 24' => [1000000, 17000, 0.35, 0.0625, 24,  6_711.77,  0.01],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 25' => [1000000, 17000, 0.35, 0.0625, 25,  6_596.69,  0.01],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 26' => [1000000, 17000, 0.35, 0.0625, 26,  6_491.13,  0.98],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 27' => [1000000, 17000, 0.35, 0.0625, 27,  6_394.00,  2.82],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 28' => [1000000, 17000, 0.35, 0.0625, 28,  6_304.36,  5.45],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 29' => [1000000, 17000, 0.35, 0.0625, 29,  6_221.45,  8.74],
        'TCP 1,000,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 30' => [1000000, 17000, 0.35, 0.0625, 30,  6_144.62, 12.56],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 21' => [1100000, 17000, 0.35, 0.0625, 21,  7_848.87,  0.01],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 22' => [1100000, 17000, 0.35, 0.0625, 22,  7_677.21,  0.01],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 23' => [1100000, 17000, 0.35, 0.0625, 23,  7_522.63,  0.02],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 24' => [1100000, 17000, 0.35, 0.0625, 24,  7_382.95,  0.01],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 25' => [1100000, 17000, 0.35, 0.0625, 25,  7_256.36,  0.01],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 26' => [1100000, 17000, 0.35, 0.0625, 26,  7_140.24,  1.08],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 27' => [1100000, 17000, 0.35, 0.0625, 27,  7_033.40,  3.11],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 28' => [1100000, 17000, 0.35, 0.0625, 28,  6_934.80,  5.98],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 29' => [1100000, 17000, 0.35, 0.0625, 29,  6_843.60,  9.59],
        'TCP 1,100,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 30' => [1100000, 17000, 0.35, 0.0625, 30,  6_759.08, 13.82],
        'TCP 1,200,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 21' => [1200000, 17000, 0.35, 0.0625, 21,  8_562.41,  0.02],
        'TCP 1,200,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 22' => [1200000, 17000, 0.35, 0.0625, 22,  8_375.14,  0.01],
        'TCP 1,200,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 23' => [1200000, 17000, 0.35, 0.0625, 23,  8_206.50,  0.01],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 21' => [1300000, 17000, 0.35, 0.0625, 21,  9_276.69,  0.76],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 22' => [1300000, 17000, 0.35, 0.0625, 22,  9_066.06,  7.00],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 23' => [1300000, 17000, 0.35, 0.0625, 23,  8_889.38,  1.0],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 24' => [1300000, 17000, 0.35, 0.0625, 24,  8_731.25,  5.95],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 25' => [1300000, 17000, 0.35, 0.0625, 25,  8_585.70, 10.00],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 26' => [1300000, 17000, 0.35, 0.0625, 26,  8_450.47, 10.73],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 27' => [1300000, 17000, 0.35, 0.0625, 27,  8_324.80,  8.93],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 28' => [1300000, 17000, 0.35, 0.0625, 28,  8_207.27,  4.53],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 29' => [1300000, 17000, 0.35, 0.0625, 29,  8_096.90,  2.33],
        'TCP 1,300,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 30' => [1300000, 17000, 0.35, 0.0625, 30,  7_992.01, 12.31],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 21' => [1400000, 17000, 0.35, 0.0625, 21,  9_990.22,  0.75],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 22' => [1400000, 17000, 0.35, 0.0625, 22,  9_764.99,  6.00],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 23' => [1400000, 17000, 0.35, 0.0625, 23,  9_560.25, 14.00],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 24' => [1400000, 17000, 0.35, 0.0625, 24,  9_380.55, 15.94],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 25' => [1400000, 17000, 0.35, 0.0625, 25,  9_214.74, 20.64],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 26' => [1400000, 17000, 0.35, 0.0625, 26,  9_060.71, 28.25],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 27' => [1400000, 17000, 0.35, 0.0625, 27,  8_915.60, 39.95],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 28' => [1400000, 17000, 0.35, 0.0625, 28,  8_779.09, 54.64],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 29' => [1400000, 17000, 0.35, 0.0625, 29,  8_649.75, 72.50],
        'TCP 1,400,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 30' => [1400000, 17000, 0.35, 0.0625, 30,  8_524.95, 95.10],
        'TCP 1,500,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 21' => [1500000, 17000, 0.35, 0.0625, 21, 10_703.76,  0.77],
        'TCP 1,500,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 22' => [1500000, 17000, 0.35, 0.0625, 22, 10_463.91,  5.02],
        'TCP 1,500,000 GMI 17,000 GMI Multiplier 35% Interest Rate 6.25% Term 23' => [1500000, 17000, 0.35, 0.0625, 23, 10_231.13, 27.0],
    ];
});

it('computes monthly amortization based on Excel MA sheet', function (
    float $tcp,
    float $gmi,
    float $gmiMultiplier,
    float $interest,
    int $termYears,
    float $expectedAmortization,
    float $precision
) {
//    dd($gmi, $gmiMultiplier, $interest, $termYears, $expectedAmortization);
    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );
    $property = new FlexibleFakeProperty(
        total_contract_price: $tcp
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        income_requirement_multiplier: $gmiMultiplier,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);
    $monthly = $service->getMonthlyAmortization();
    $amount = $monthly->inclusive()->getAmount()->toFloat();
    expect($amount)->toBeCloseTo($expectedAmortization, $precision);
})->with('monthly amortization scenarios');

it('computes present value from disposable income (max loanable amount)', function () {
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;
    $termYears = 21;
    $expectedLoanable = 833_878.13; // Based on Excel MA worksheet

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: 1_000_000 // irrelevant for this test
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        income_requirement_multiplier: $multiplier,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);
    $presentValue = $service->getPresentValueFromDisposable();

    expect($presentValue->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedLoanable, 0.01);
});

it('computes required equity correctly when TCP exceeds loanable amount', function () {
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;
    $termYears = 21;

    // The max affordable TCP based on Excel is ~833,878.13
    $tcpWith10PercentIncrease = 833_878.13 * 1.10; // ₱917,266.94

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: $tcpWith10PercentIncrease
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        income_requirement_multiplier: $multiplier,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);

    $equity = $service->computeRequiredEquity();
    $expectedEquity = $tcpWith10PercentIncrease - 833_878.13;

    expect($equity)->toBeInstanceOf(Equity::class)
        ->and($equity->toPrice()->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedEquity, 0.01);
});

it('computes required equity correctly with a 10% down payment', function () {
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;
    $termYears = 21;
    $downPaymentPercent = 0.10;

    // Based on Excel: Max TCP affordable is ₱833,878.13
    $tcp = 833_878.13 * 1.20; // ₱1,000,653.76
    $expectedEquity = $tcp - ($tcp * $downPaymentPercent) - 833_878.13;

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: $tcp
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        percent_down_payment: $downPaymentPercent,
        income_requirement_multiplier: $multiplier,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);
    $equity = $service->computeRequiredEquity();

    expect($equity)->toBeInstanceOf(Equity::class)
        ->and($equity->toPrice()->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedEquity, 0.01);
});

it('returns qualification result for qualified borrower with no equity gap', function () {
    $tcp = 926_500.00;
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;
    $termYears = 21;
    $downPaymentPercent = 0.10;

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: $tcp
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        percent_down_payment: $downPaymentPercent,
        income_requirement_multiplier: $multiplier,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);

    $result = $service->getQualificationResult();

    expect($result->qualifies)->toBeTrue()
        ->and($result->gap)->toBe(0.0)
        ->and($result->suggested_equity->isZero())->toBeTrue()
        ->and($result->actual_down_payment)->toBeCloseTo($tcp * $downPaymentPercent, 0.01)
        ->and($result->required_loanable + $result->actual_down_payment)->toBeCloseTo($tcp, 0.01)
        ->and($result->suggested_down_payment_percent)->toBeLessThanOrEqual($downPaymentPercent)
        ->and($result->required_down_payment->inclusive()->getAmount()->toFloat())->toBeLessThanOrEqual($result->actual_down_payment)
    ;
});

it('returns qualification result for unqualified borrower with required equity', function () {
    $tcp = 1_025_670.10; // ~2.5% higher
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;
    $termYears = 21;
    $downPaymentPercent = 0.10;

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: $tcp
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        percent_down_payment: $downPaymentPercent,
        income_requirement_multiplier: $multiplier,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);

    $result = $service->getQualificationResult();

    expect($result->qualifies)->toBeFalse()
        ->and($result->suggested_equity->inclusive()->getAmount()->toFloat())->toBeGreaterThan(0)
        ->and($result->gap)->toBeGreaterThan(0)
        ->and($result->actual_down_payment)->toBeCloseTo($tcp * $downPaymentPercent, 0.01)
        ->and($result->required_loanable + $result->actual_down_payment)->toBeCloseTo($tcp, 0.01)
        ->and($result->suggested_down_payment_percent)->toBeGreaterThan($downPaymentPercent)
        ->and($result->required_down_payment->inclusive()->getAmount()->toFloat())->toBeCloseTo($tcp - $result->affordable_loanable, 0.01);
});

it('computes required cash out and balance miscellaneous fee correctly', function () {
    $tcp = 1_000_000;
    $downPaymentPercent = 0.10;
    $percentMiscFee = 0.085;
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;
    $termYears = 21;

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: $tcp
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        percent_down_payment: $downPaymentPercent,
        income_requirement_multiplier: $multiplier,
        percent_miscellaneous_fees: $percentMiscFee,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);
    $result = $service->getQualificationResult();

    $expectedDownPayment = $tcp * $downPaymentPercent; // 100,000
    $expectedUpfrontMF = $downPaymentPercent * $percentMiscFee * $tcp; // 8,500
    $expectedCashOut = $expectedDownPayment + $expectedUpfrontMF; // 108,500

    $expectedBalanceMF = (1 - $downPaymentPercent) * $percentMiscFee * $tcp; // 76,500

    expect($result->required_cash_out)->toBeInstanceOf(Price::class)
        ->and($result->required_cash_out->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedCashOut, 0.01)
        ->and($result->balance_miscellaneous_fee->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedBalanceMF, 0.01);
});

it('computes monthly share of miscellaneous fee correctly', function () {
    $tcp = 1_000_000;
    $downPaymentPercent = 0.10;
    $percentMiscFee = 0.085;
    $termYears = 21;
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: $tcp
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        percent_down_payment: $downPaymentPercent,
        income_requirement_multiplier: $multiplier,
        percent_miscellaneous_fees: $percentMiscFee,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);

    $monthlyMF = $service->getMonthlyMiscellaneousFeeShare();

    $expectedBalanceMF = (1 - $downPaymentPercent) * $percentMiscFee * $tcp;
    $expectedMonthlyShare = $expectedBalanceMF / ($termYears * 12);

    expect($monthlyMF)->toBeInstanceOf(Price::class)
        ->and($monthlyMF->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedMonthlyShare, 0.01);
});

it('computes monthly amortization breakdown correctly with real add-ons', function () {
    $tcp = 1_000_000;
    $downPaymentPercent = 0.10;
    $percentMiscFee = 0.085;
    $termYears = 21;
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;

    // Actual monthly add-ons
    $mri = 150.0;
    $fire = 75.0;

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: $tcp
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        percent_down_payment: $downPaymentPercent,
        income_requirement_multiplier: $multiplier,
        percent_miscellaneous_fees: $percentMiscFee,
        monthly_mri: $mri,
        monthly_fi: $fire,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);
    $breakdown = $service->getMonthlyAmortizationBreakdown();

    $balancePrincipal = $tcp * (1 - $downPaymentPercent);
    $balanceMF = (1 - $downPaymentPercent) * $percentMiscFee * $tcp;
    $months = $termYears * 12;
    $monthlyRate = round($interest / 12, 15);

    $expectedPrincipal = $monthlyRate === 0
        ? $balancePrincipal / $months
        : ($balancePrincipal * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));

    $expectedMF = $balanceMF / $months;
    $expectedAddOns = $mri + $fire;
    $expectedTotal = $expectedPrincipal + $expectedMF + $expectedAddOns;

    expect($breakdown->principal->inclusive()->getAmount()->toFloat())->toBeCloseTo($expectedPrincipal, 0.01)
        ->and($breakdown->mf->inclusive()->getAmount()->toFloat())->toBeCloseTo($expectedMF, 0.01)
        ->and($breakdown->add_ons->inclusive()->getAmount()->toFloat())->toBeCloseTo($expectedAddOns, 0.01)
        ->and($breakdown->total->inclusive()->getAmount()->toFloat())->toBeCloseTo($expectedTotal, 0.01);
});

it('injects monthly amortization breakdown into qualification result', function () {
    $tcp = 1_000_000;
    $downPaymentPercent = 0.10;
    $percentMiscFee = 0.085;
    $termYears = 21;
    $gmi = 17_000;
    $multiplier = 0.35;
    $interest = 0.0625;
    $mri = 941.90;
    $fire = 0.0;

    $buyer = new FlexibleFakeBuyer(
        gross_monthly_income: $gmi,
        joint_maximum_term_allowed: $termYears
    );

    $property = new FlexibleFakeProperty(
        total_contract_price: $tcp
    );

    $order = new FlexibleFakeOrder(
        interest: $interest,
        percent_down_payment: $downPaymentPercent,
        income_requirement_multiplier: $multiplier,
        percent_miscellaneous_fees: $percentMiscFee,
        monthly_mri: $mri,
        monthly_fi: $fire,
    );

    $inputs = MortgageParticulars::fromBooking($buyer, $property, $order);
    $service = new MortgageComputation($inputs);
    $result = $service->getQualificationResult();
    $breakdown = $result->monthly_amortization_breakdown;

    // === Dynamic Expected Values ===
    $balancePrincipal = $tcp * (1 - $downPaymentPercent); // ₱900,000
    $balanceMF = (1 - $downPaymentPercent) * $percentMiscFee * $tcp; // ₱76,500
    $months = $termYears * 12; // 252 months
    $monthlyRate = $interest / 12; // 0.005208333333...

    $expectedPrincipal = $monthlyRate === 0
        ? $balancePrincipal / $months
        : ($balancePrincipal * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));

    $expectedMF = $balanceMF / $months;
    $expectedAddOns = $mri + $fire;
    $expectedTotal = $expectedPrincipal + $expectedMF + $expectedAddOns;

    // === Assertions ===
    expect($breakdown->principal->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedPrincipal, 0.01)
        ->and($breakdown->mf->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedMF, 0.01)
        ->and($breakdown->add_ons->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedAddOns, 0.01)
        ->and($breakdown->total->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedTotal, 0.01);
});
