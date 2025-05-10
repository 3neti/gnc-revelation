<?php

use App\DataObjects\MortgageTerm;
use App\Services\PurchasePlanCalculator;
use LBHurtado\Mortgage\ValueObjects\DownPayment;

dataset('rdg qualification cases', function () {
    return [
        // Based on Excel Matrix: TCP ₱750,000, Term 20 yrs, GMI ₱18,000
        'RDG - TCP 750k, 20yr, GMI 18k' => [
            750000,             // TCP (Total Contract Price)
            0.0625,             // Interest Rate (6.25%)
            20,                 // Term in years
            5481.96,             // Expected Monthly Amortization from Excel PMT
            18000,              // Gross Monthly Income
            0.35,               // Disposable Income Multiplier
            6300.0,             // Expected Disposable Income (18k * 0.35)
            15662.74,           // Income Required (Amort / Multiplier)
            true,               // Expected Qualification Result
            86.0,               // Margin for equity (manual Excel-to-JS comparison)
        ],

//        // Based on Excel Matrix: TCP ₱1.2M, Term 25 yrs, GMI ₱30,000
//        'RDG - TCP 1.2M, 25yr, GMI 30k' => [
//            1200000,
//            0.0625,
//            25,
//            8005.0,
//            30000,
//            0.35,
//            10500.0,
//            22871.43,
//            true,
//            255,
//        ],
//
//        // Based on Excel Matrix: TCP ₱1.5M, Term 30 yrs, GMI ₱35,000
//        'RDG - TCP 1.5M, 30yr, GMI 35k' => [
//            1500000,
//            0.0625,
//            30,
//            9620.0,
//            35000,
//            0.35,
//            12250.0,
//            27485.71,
//            true,
//            1098,
//        ],
//
//        // Disqualifier: TCP ₱2M, Term 30 yrs, GMI ₱30,000 (Income not enough)
//        'RDG - TCP 2M, 30yr, GMI 30k' => [
//            2000000,
//            0.0625,
//            30,
//            12826.0,
//            30000,
//            0.35,
//            10500.0,
//            36645.71,
//            false,
//            1470,
//        ],
    ];
});

it('evaluates RDG borrower qualification using PurchasePlanCalculator', function (
    float $tcp,
    float $interestRate,
    int $termYears,
    float $expectedMonthlyAmort,
    float $grossIncome,
    float $multiplier,
    float $expectedDisposable,
    float $expectedIncomeRequired,
    bool $expectedToQualify,
    float $precision
) {
    $dp = new DownPayment($tcp, 0);

    $calc = new PurchasePlanCalculator(
        downPayment: $dp,
        interestRate: $interestRate,
        term: new MortgageTerm($termYears),
        disposableMultiplier: $multiplier
    );

    $result = $calc->getQualificationResultFromGrossIncome($grossIncome);

    dump([
        'qualifies' => $result->qualifies,
        'monthly_amortization' => $result->monthly_amortization->inclusive()->getAmount()->toFloat(),
        'disposable_income' => $result->disposable_income->getAmount()->toFloat(),
        'income_required' => $result->income_required->getAmount()->toFloat(),
        'gap' => $result->gap,
        'reason' => $result->reason,
        'suggested_equity' => $result->suggested_equity->inclusive()->getAmount()->toFloat(),
    ]);

    // Inject these to test:
    $loanable = 750000;
    $monthlyRate = 0.0625 / 12;
    $months = 240;

    $baseMonthly = ($loanable * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));
    dump('Manual Base Monthly:', round($baseMonthly, 2)); // Should match 5452.00

    expect($result->qualifies)
        ->toBe($expectedToQualify)
//        ->and($result->monthly_amortization->inclusive()->getAmount()->toFloat())->toBeCloseTo($expectedMonthlyAmort, $precision)
//        ->and($result->disposable_income->getAmount()->toFloat())->toBeCloseTo($expectedDisposable, $precision)
//        ->and($result->income_required->getAmount()->toFloat())->toBeCloseTo($expectedIncomeRequired, $precision)
    ;
})->with('rdg qualification cases');

//it('calculates monthly amortization without interest (zero interest)', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 1_200_000,
//        interestRate: 0.0,
//        term: new \App\DataObjects\MortgageTerm(20)
//    );
//
//    $monthly = $calc->monthlyAmortization();
//
//    expect($monthly)->toBeInstanceOf(Price::class)
//        ->and($monthly->inclusive()->getAmount()->toFloat())->toBeCloseTo(5000.0, 0);
//});
//
//it('includes add-on and deductible fees in PurchasePlanCalculator', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 1_000_000,
//        interestRate: 0.06,
//        term: new MortgageTerm(30)
//    );
//
//    $calc->addAddOnFee('fire insurance', 500);
//    $calc->addAddOnFee('MRI', 300);
//    $calc->addDeductibleFee('rebate', 200);
//
//    $final = $calc->monthlyAmortization()->inclusive()->getAmount()->toFloat();
//
//    expect($final)->toBeGreaterThan(5996); // 5995.51 + 800 - 200 approx
//});
//
//it('calculates present value using PurchasePlanCalculator', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 1_000_000,
//        interestRate: 0.06,
//        term: new \App\DataObjects\MortgageTerm(30)
//    );
//
//    $presentValue = $calc->presentValue();
//
//    expect($presentValue)->toBeInstanceOf(Price::class)
//        ->and($presentValue->inclusive()->getAmount()->toFloat())->toBeGreaterThan(600_000);
//});
//
//it('computes required equity when borrower does not qualify (PurchasePlanCalculator)', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 2_000_000,
//        interestRate: 0.06,
//        term: new MortgageTerm(30),
//    );
//
//    $actualDisposable = Money::of(8000, 'PHP'); // below amortization threshold
//
//    $equity = $calc->computeRequiredEquity($actualDisposable);
//
//    expect($equity)->toBeInstanceOf(Price::class)
//        ->and($equity->inclusive()->getAmount()->toFloat())->toBeGreaterThan(0);
//});
//
//it('computes amortization precisely at 6% over 30 years', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 1_000_000,
//        interestRate: 0.06,
//        term: new MortgageTerm(30)
//    );
//
//    $amort = $calc->monthlyAmortization();
//
//    expect($amort->inclusive()->getAmount()->toFloat())->toBeCloseTo(5995.51, 0.01); // Known accurate
//});
//
//it('adjusts income requirement based on disposable income multiplier', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 1_000_000,
//        interestRate: 0.06,
//        term: new MortgageTerm(30),
//        disposableMultiplier: 0.25 // tighter margin
//    );
//
//    $income = $calc->incomeRequirement();
//
//    expect($income)->toBeInstanceOf(Money::class)
//        ->and($income->getAmount()->toFloat())->toBeGreaterThan(23982); // 5995.51 / 0.25
//});
//
//it('handles zero or negative disposable income gracefully', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 1_200_000,
//        interestRate: 0.05,
//        term: new MortgageTerm(30),
//    );
//
//    $result = $calc->getQualificationResult(Money::of(0, 'PHP'));
//
//    expect($result->qualifies)->toBeFalse()
//        ->and($result->gap)->toBeGreaterThan(0)
//        ->and($result->suggested_equity->inclusive()->getAmount()->toFloat())->toBeGreaterThan(0);
//});
//
//it('generates qualification result for qualified borrower', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 1_200_000,
//        interestRate: 0.05,
//        term: new MortgageTerm(30),
//        disposableMultiplier: 0.35
//    );
//
//    $gross = 35_000;
//    $disposable = Money::of($gross * 0.35, 'PHP');
//
//    $result = $calc->getQualificationResult($disposable);
//
//    expect($result->qualifies)->toBeTrue()
//        ->and($result->gap)->toBe(0.0)
//        ->and($result->reason)->toBe('Sufficient disposable income')
//        ->and($result->income_required->isPositive())->toBeTrue()
//        ->and($result->suggested_equity->inclusive()->getAmount()->toFloat())->toBe(0.0);
//});
//
//it('generates qualification result for unqualified borrower', function () {
//    $calc = new PurchasePlanCalculator(
//        principal: 2_000_000,
//        interestRate: 0.06,
//        term: new MortgageTerm(30),
//        disposableMultiplier: 0.35
//    );
//
//    $disposable = Money::of(8000, 'PHP');
//
//    $result = $calc->getQualificationResult($disposable);
//
//    expect($result->qualifies)->toBeFalse()
//        ->and($result->gap)->toBeGreaterThan(0)
//        ->and($result->reason)->toBe('Disposable income below amortization')
//        ->and($result->suggested_equity)->toBeInstanceOf(Price::class);
//});
