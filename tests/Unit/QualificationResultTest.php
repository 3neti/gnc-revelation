<?php

use App\Services\MortgageCalculatorService;
use App\DataObjects\MortgageTerm;
use Brick\Money\Money;
use Whitecube\Price\Price;

dataset('qualification cases', function () {
    return [
        'qualifies comfortably' => [
            1_200_000, 0.06, 30, 35_000, 0.35,
            12_250.0, 7194.61, true, 0.05, 0.0, 1.0
        ],
        'does not qualify' => [
            2_000_000, 0.06, 30, 30_000, 0.35,
            10_500.0, 11991.01, false, 0.05, 248688.05, 100.0
        ],
    ];
});

it('evaluates mortgage qualification results correctly', function (
    float $principal,
    float $interest,
    int $term,
    float $grossIncome,
    float $multiplier,
    float $expectedDisposable,
    float $expectedMonthly,
    bool $expectedToQualify,
    float $precision,
    float $expectedEquity,
    float $equityPrecision
) {
    $calc = new MortgageCalculatorService(
        principal: $principal,
        interestRate: $interest,
        term: new MortgageTerm($term),
        disposableMultiplier: $multiplier
    );

    $disposable = $grossIncome * $multiplier;
    $result = $calc->getQualificationResult(Money::of($disposable, 'PHP'));

    expect($result->qualifies)->toBe($expectedToQualify)
        ->and($result->disposable_income->getAmount()->toFloat())
        ->toBeCloseTo($expectedDisposable, $precision)
        ->and($result->monthly_amortization->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedMonthly, $precision)
        ->and($result->suggested_equity->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedEquity, $equityPrecision)
        ->and($result->gap)->toBeFloat()
        ->and($result->suggested_equity)->toBeInstanceOf(Price::class)
        ->and($result->income_required)->toBeInstanceOf(Money::class);
})->with('qualification cases');
