<?php

use App\Services\MortgageCalculatorService;
use App\DataObjects\MortgageTerm;
use Whitecube\Price\Price;
use Brick\Money\Money;

it('calculates monthly amortization without interest', function () {
    $calc = new MortgageCalculatorService(
        principal: 1200000,
        interestRate: 0.0,
        term: new MortgageTerm(20),
    );

    $monthly = $calc->monthlyAmortization();

    expect($monthly)->toBeInstanceOf(Price::class)
        ->and($monthly->inclusive()->getAmount()->toFloat())->toBeCloseTo(5000.0, 0);
});

it('calculates monthly amortization with interest', function () {
    $calc = new MortgageCalculatorService(
        principal: 1000000,
        interestRate: 0.06,
        term: new MortgageTerm(30),
    );

    $monthly = $calc->monthlyAmortization();

    expect($monthly->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo(5995.51, 0.01); // ✅ precise match
});

it('includes add-on fees and deductibles in final payment', function () {
    $calc = new MortgageCalculatorService(
        principal: 1000000,
        interestRate: 0.06,
        term: new MortgageTerm(30)
    );

    $calc->addAddOnFee('fire insurance', 500);
    $calc->addAddOnFee('mri', 300);
    $calc->addDeductibleFee('promo discount', 200);

    $final = $calc->monthlyAmortization()->inclusive()->getAmount()->toFloat();

    expect($final)->toBeGreaterThan(5996); // base + 800 - 200
});

it('calculates income requirement from amortization', function () {
    $calc = new MortgageCalculatorService(
        principal: 1500000,
        interestRate: 0.05,
        term: new MortgageTerm(25),
        disposableMultiplier: 0.30
    );

    $income = $calc->incomeRequirement();

    expect($income)->toBeInstanceOf(Money::class)
        ->and($income->isPositive())->toBeTrue();
});

it('calculates present value from future payments', function () {
    $calc = new MortgageCalculatorService(
        principal: 1000000,
        interestRate: 0.06,
        term: new MortgageTerm(30)
    );

    $presentValue = $calc->presentValue();

    expect($presentValue)->toBeInstanceOf(Price::class)
        ->and($presentValue->inclusive()->getAmount()->toFloat())->toBeGreaterThan(600000);
});

it('computes required equity when borrower does not qualify', function () {
    $calc = new MortgageCalculatorService(
        principal: 2_000_000,
        interestRate: 0.06,
        term: new MortgageTerm(30),
    );

    $actualDisposable = Money::of(8000, 'PHP'); // too low for amortization

    $equity = $calc->computeRequiredEquity($actualDisposable);

    expect($equity)->toBeInstanceOf(Price::class)
        ->and($equity->inclusive()->getAmount()->toFloat())->toBeGreaterThan(0);
});

it('generates qualification result for qualified borrower', function () {
    $calc = new MortgageCalculatorService(
        principal: 1_200_000,
        interestRate: 0.05,
        term: new MortgageTerm(30),
        disposableMultiplier: 0.35
    );

    $gross = 35_000;
    $disposable = Money::of($gross * 0.35, 'PHP');

    $result = $calc->getQualificationResult($disposable);

    expect($result->qualifies)->toBeTrue()
        ->and($result->gap)->toBe(0.0)
        ->and($result->reason)->toBe('Sufficient disposable income')
        ->and($result->income_required->isPositive())->toBeTrue()
        ->and($result->suggested_equity->inclusive()->getAmount()->toFloat())->toBe(0.0);
});

it('generates qualification result for unqualified borrower', function () {
    $calc = new MortgageCalculatorService(
        principal: 2_000_000,
        interestRate: 0.06,
        term: new MortgageTerm(30),
        disposableMultiplier: 0.35
    );

    $disposable = Money::of(8000, 'PHP');

    $result = $calc->getQualificationResult($disposable);

    expect($result->qualifies)->toBeFalse()
        ->and($result->gap)->toBeGreaterThan(0)
        ->and($result->reason)->toBe('Disposable income below amortization')
        ->and($result->suggested_equity)->toBeInstanceOf(Price::class);
});
