<?php

use App\Classes\Buyer;
use App\Data\LoanProductData;
use App\DataObjects\MortgageTerm;
use App\Services\LoanMatcherService;
use App\Services\BorrowingRulesService;
use Brick\Money\Money;
use Whitecube\Price\Price;

dataset('loan matcher combinations', function () {
    return [
        // Format: GMI, Multiplier, Age, Code, TCP, Term, Interest, ExpectedQualifies,
        //         ExpectedDisposable, ExpectedAmort, ExpectedGap,
        //         AmortPrecision, DisposablePrecision, GapPrecision

        'Buyer 1 - RDG750' => [
            18000, 0.35, 30, 'RDG750', 750000, 20, 0.0625,
            true, 6300.00, 5452.00, 0.00,
            30.0, 0.01, 0.1
        ],

        'Buyer 2 - RDG1500' => [
            30000, 0.35, 30, 'RDG1500', 1500000, 30, 0.0625,
            true, 10500.00, 9620.00, 0.00,
            385, 0.01, 0.1
        ],

        'Buyer 3 - RDG2000' => [
            40000, 0.30, 30, 'RDG2000', 2000000, 30, 0.0625,
            false, 12000.00, 12826.00, 826.00,
            512, 0.01, 512
        ],

        'Buyer 4 - RDG750' => [
            25000, 0.30, 30, 'RDG750', 750000, 20, 0.0625,
            true, 7500.00, 5452.00, 0.00,
            30, 0.01, 0.1
        ],

        'Buyer 4 - RDG1200' => [
            25000, 0.30, 30, 'RDG1200', 1200000, 25, 0.0625,
            false, 7500.00, 8005.00, 505.00,
            89, 0.01, 89
        ],
    ];
});

it('matches buyer to loan product and logs details with precision', function (
    float $grossIncome,
    float $multiplier,
    int $age,
    string $productCode,
    float $tcp,
    int $term,
    float $interestRate,
    bool $expectedQualifies,
    float $expectedDisposable,
    float $expectedAmort,
    float $expectedGap,
    float $amortPrecision,
    float $disposablePrecision,
    float $gapPrecision
) {
    $rules = app(\App\Services\BorrowingRulesService::class);

    $buyer = new \App\Classes\Buyer($rules);
    $buyer->setAge($age)
        ->setMonthlyGrossIncome(new Price(Money::of($grossIncome, 'PHP')))
        ->setIncomeRequirementMultiplier($multiplier);

    $product = new LoanProductData(
        code: $productCode,
        name: $productCode,
        tcp: $tcp,
        interest_rate: $interestRate,
        max_term_years: $term,
        max_loanable_percent: 1.0,
        disposable_income_multiplier: $multiplier
    );

    $result = (new LoanMatcherService())
        ->match($buyer, collect([$product]))
        ->first();

    $actualTerm = min($term, $buyer->getJointMaximumTermAllowed());
    $actualAmort = $result->monthly_amortization->inclusive()->getAmount()->toFloat();
    $actualDisposable = $buyer->getJointMonthlyDisposableIncome()->inclusive()->getAmount()->toFloat();
    $actualGap = $result->gap;

    // Log
    echo "→ {$productCode} | Age: {$age} | Term: {$actualTerm} | " .
        "Amort: {$actualAmort} vs {$expectedAmort} | " .
        "Disposable: {$actualDisposable} vs {$expectedDisposable} | " .
        "Gap: {$actualGap} vs {$expectedGap} | " .
        ($result->qualified ? '✅' : '❌') . PHP_EOL;

    expect($result->qualified)->toBe($expectedQualifies)
        ->and($actualAmort)->toBeCloseTo($expectedAmort, $amortPrecision)
        ->and($actualDisposable)->toBeCloseTo($expectedDisposable, $disposablePrecision)
        ->and($actualGap)->toBeCloseTo($expectedGap, $gapPrecision);
})->with('loan matcher combinations');
