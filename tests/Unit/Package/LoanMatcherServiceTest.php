<?php

use LBHurtado\Mortgage\Classes\{Buyer, LendingInstitution, Order, Property};
use LBHurtado\Mortgage\Services\{AgeService, BorrowingRulesService};
use LBHurtado\Mortgage\Transformers\MatchResultTransformer;
use LBHurtado\Mortgage\Services\LoanMatcherService;
use LBHurtado\Mortgage\Data\Match\LoanProductData;
use LBHurtado\Mortgage\Data\Match\MatchResultData;
use Whitecube\Price\Price;
use Brick\Money\Money;

beforeEach(function () {
    $this->rules = new BorrowingRulesService(new AgeService());
});

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
    $rules = app(BorrowingRulesService::class);

    $buyer = new Buyer($rules);
    $buyer->setAge($age)
        ->setMonthlyGrossIncome(new Price(Money::of($grossIncome, 'PHP')))
        ->setIncomeRequirementMultiplier($multiplier)
    ;


    $buyer = app(Buyer::class)
        ->setAge($age)
        ->setMonthlyGrossIncome($grossIncome)
        ->setLendingInstitution(new LendingInstitution('hdmf'))
        ->setIncomeRequirementMultiplier($multiplier)
    ;

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
        ->and($actualGap)->toBeCloseTo($expectedGap, $gapPrecision)
    ;
})->with('loan matcher combinations');

dataset('loan product options with expectation', [
    'RDG750 - should qualify' => [
        new LoanProductData(
            code: 'P750',
            name: 'RDG750',
            tcp: 750_000,
            interest_rate: 0.0625,
            max_term_years: 20,
            max_loanable_percent: 1.0,
            disposable_income_multiplier: 0.35
        ),
        true
    ],
    'RDG1000 - should qualify' => [
        new LoanProductData(
            code: 'P1000',
            name: 'RDG1000',
            tcp: 1_000_000,
            interest_rate: 0.0625,
            max_term_years: 25,
            max_loanable_percent: 1.0,
            disposable_income_multiplier: 0.35
        ),
        true
    ],
    'RDG1500 - should qualify' => [
        new LoanProductData(
            code: 'P1500',
            name: 'RDG1500',
            tcp: 1_500_000,
            interest_rate: 0.0625,
            max_term_years: 30,
            max_loanable_percent: 1.0,
            disposable_income_multiplier: 0.35
        ),
        true
    ],
    'RDG2000 - should not qualify' => [
        new LoanProductData(
            code: 'P2000',
            name: 'RDG2000',
            tcp: 2_000_000,
            interest_rate: 0.0625,
            max_term_years: 30,
            max_loanable_percent: 1.0,
            disposable_income_multiplier: 0.35
        ),
        false
    ],
]);

it('evaluates borrower qualification for each loan product explicitly', function (
    LoanProductData $product,
    bool $expectedQualifies
) {
    $buyer = app(Buyer::class)
        ->setAge(30)
        ->setMonthlyGrossIncome(30_000)
        ->setIncomeRequirementMultiplier(0.35)
        ->setLendingInstitution(new LendingInstitution('hdmf'));

    $results = (new LoanMatcherService())->match($buyer, collect([$product]));
    $match = $results->first();

    expect($match)->toBeInstanceOf(MatchResultData::class)
        ->and($match->qualified)->toBe($expectedQualifies);

    if ($expectedQualifies) {
        $disposable = $buyer->getJointMonthlyDisposableIncome()->inclusive()->getAmount()->toFloat();
        $amort = $match->monthly_amortization->inclusive()->getAmount()->toFloat();

        expect($amort)->toBeLessThanOrEqual($disposable)
            ->and($match->gap)->toBe(0.0);
    } else {
        expect($match->gap)->toBeGreaterThan(0.0);
    }

    echo "→ {$product->code} | " .
        ($match->qualified ? '✅ qualified' : '❌ not qualified') .
        PHP_EOL;
})->with('loan product options with expectation');

it('returns only qualified loan products from a set', function () {
    $products = collect([
        new LoanProductData(
            code: 'P750',
            name: 'RDG750',
            tcp: 750_000,
            interest_rate: 0.0625,
            max_term_years: 20,
            max_loanable_percent: 1.0,
            disposable_income_multiplier: 0.35
        ),
        new LoanProductData(
            code: 'P1000',
            name: 'RDG1000',
            tcp: 1_000_000,
            interest_rate: 0.0625,
            max_term_years: 25,
            max_loanable_percent: 1.0,
            disposable_income_multiplier: 0.35
        ),
        new LoanProductData(
            code: 'P1500',
            name: 'RDG1500',
            tcp: 1_500_000,
            interest_rate: 0.0625,
            max_term_years: 30,
            max_loanable_percent: 1.0,
            disposable_income_multiplier: 0.35
        ),
        new LoanProductData(
            code: 'P2000',
            name: 'RDG2000',
            tcp: 2_000_000,
            interest_rate: 0.0625,
            max_term_years: 30,
            max_loanable_percent: 1.0,
            disposable_income_multiplier: 0.35
        ),
    ]);

    $buyer = app(Buyer::class)
        ->setAge(30)
        ->setMonthlyGrossIncome(30_000)
        ->setIncomeRequirementMultiplier(0.35)
        ->setLendingInstitution(new LendingInstitution('hdmf'));

    $results = (new LoanMatcherService())->match($buyer, $products);

    /** @var \Illuminate\Support\Collection<int, MatchResultData> $qualified */
    $qualified = $results->filter(fn (MatchResultData $result) => $result->qualified);

    expect($qualified->count())->toBe(3)
        ->and($qualified->pluck('product_code'))->not->toContain('P2000');

    $qualifiedResults = (new LoanMatcherService())
        ->matchQualifiedOnly($buyer, $products);

    expect($qualifiedResults)->toHaveCount(3);
});

it('transforms MatchResultData into a response array', function () {
    $result = new MatchResultData(
        qualified: true,
        product_code: 'RDG750',
        monthly_amortization: Price::of(5452.00, 'PHP'),
        income_required: Price::of(15577.14, 'PHP'),
        suggested_equity: Price::of(0.00, 'PHP'),
        gap: 0.00,
        reason: 'Sufficient disposable income'
    );

    $transformed = MatchResultTransformer::transform($result);

    expect($transformed)->toBeArray()
        ->and($transformed['qualified'])->toBeTrue()
        ->and($transformed['product_code'])->toBe('RDG750')
        ->and($transformed['monthly_amortization'])->toBe(5452.00)
        ->and($transformed['income_required'])->toBe(15577.14)
        ->and($transformed['suggested_equity'])->toBe(0.00)
        ->and($transformed['income_gap'])->toBe(0.00)
        ->and($transformed['reason'])->toBe('Sufficient disposable income');
});
