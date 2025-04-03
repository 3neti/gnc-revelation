<?php

use App\Services\MortgageCalculatorService;
use App\DataObjects\MortgageTerm;
use Brick\Money\Money;
use Whitecube\Price\Price;

dataset('fee qualification cases', function () {
    return [
        'basic with fees qualifies' => [
            1_200_000, 0.05, 20, 40000, 0.35,
            ['fire insurance' => 500, 'MRI' => 300],
            ['promo' => 200],
            0.0, // <- expected amortization (to fill)
            14000.0, // <- expected disposable
            true,
        ],
        'mid-range with fees qualifies' => [
            1_750_000, 0.04, 25, 45000, 0.35,
            ['fire insurance' => 450],
            [],
            0.0,
            15750.0,
            true,
        ],
        'with deductible, borderline case' => [
            2_000_000, 0.045, 30, 30000, 0.35,
            [],
            ['rebate' => 300],
            0.0,
            10500.0,
            true,
        ],
        'high interest with fees, fails' => [
            2_500_000, 0.07, 20, 35000, 0.35,
            ['fire insurance' => 600, 'MRI' => 400],
            [],
            0.0,
            12250.0,
            false,
        ],
        'low interest, high income, qualifies easily' => [
            1_000_000, 0.025, 30, 50000, 0.35,
            [],
            [],
            0.0,
            17500.0,
            true,
        ],
    ];
});

it('calculates amortization and evaluates qualification with fees correctly', function (
    float $principal,
    float $interest,
    int $term,
    float $grossIncome,
    float $multiplier,
    array $addOns,
    array $deductibles,
    float $expectedAmortization,
    float $expectedDisposable,
    bool $expectedToQualify
) {
    $calc = new MortgageCalculatorService(
        principal: $principal,
        interestRate: $interest,
        term: new MortgageTerm($term),
        disposableMultiplier: $multiplier
    );

    foreach ($addOns as $label => $amount) {
        $calc->addAddOnFee($label, $amount);
    }

    foreach ($deductibles as $label => $amount) {
        $calc->addDeductibleFee($label, $amount);
    }

    $disposable = $grossIncome * $multiplier;
    $result = $calc->getQualificationResult(Money::of($disposable, 'PHP'));

    expect($result->qualifies)->toBe($expectedToQualify)
        ->and($result->disposable_income->getAmount()->toFloat())->toBeCloseTo($expectedDisposable, 0.01)
        ->and($result->monthly_amortization)->toBeInstanceOf(Price::class)
        // ->and($result->monthly_amortization->inclusive()->getAmount()->toFloat())->toBeCloseTo($expectedAmortization, 0.01) // Fill in expected
        ->and($result->income_required)->toBeInstanceOf(Money::class);
})->with('fee qualification cases');
