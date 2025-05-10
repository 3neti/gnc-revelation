<?php

use LBHurtado\Mortgage\Http\Controllers\LoanMatchController;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::post('/loan-match', LoanMatchController::class)->name('api.v1.loan-match');
});

dataset('loan matcher unique records', [
    'hdmf 1.0M by 49yo ₱17k gmi — expect not qualify' => [
        49, 17000, [
            [
                'code' => 'P1000',
                'name' => 'HDMF1000',
                'tcp' => 1000000,
                'interest_rate' => 0.0625,
                'max_term_years' => 21,
                'max_loanable_percent' => 1.0,
                'disposable_income_multiplier' => 0.35,
            ],
        ],
        0
    ],
    'hdmf 1.0M by 47yo ₱21k gmi — expect qualify' => [
        47, 21000, [
            [
                'code' => 'P1000',
                'name' => 'HDMF1000',
                'tcp' => 1000000,
                'interest_rate' => 0.0625,
                'max_term_years' => 23,
                'max_loanable_percent' => 1.0,
                'disposable_income_multiplier' => 0.35,
            ],
        ],
        1
    ],
    'hdmf 1.1M by 48yo ₱19k gmi — expect not qualify' => [
        48, 19000, [
            [
                'code' => 'P1100',
                'name' => 'HDMF1100',
                'tcp' => 1100000,
                'interest_rate' => 0.0625,
                'max_term_years' => 22,
                'max_loanable_percent' => 1.0,
                'disposable_income_multiplier' => 0.35,
            ],
        ],
        0
    ],
    'rcbc 1.1M by 45yo ₱24k gmi — expect qualify' => [
        45, 24000, [
            [
                'code' => 'R1100',
                'name' => 'RCBC1100',
                'tcp' => 1100000,
                'interest_rate' => 0.0625,
                'max_term_years' => 16,
                'max_loanable_percent' => 1.0,
                'disposable_income_multiplier' => 0.3,
            ],
        ],
        1
    ],
]);

it('returns expected qualified loan products', function (
    int $age,
    float $income,
    array $products,
    int $expectedCount
) {
    $payload = [
        'age' => $age,
        'monthly_income' => $income,
        'products' => $products,
    ];

    $response = $this->postJson(route('api.v1.loan-match'), $payload);

    $response->assertOk()
        ->assertJsonCount($expectedCount);

    $json = $response->json();

    expect(collect($json)->every(fn ($item) => $item['qualified'] === true))->toBeTrue();

    if ($expectedCount === 0) return;

    collect($json)->each(fn ($item) =>
    expect($item)->toHaveKeys([
        'product_code',
        'monthly_amortization',
        'income_required',
        'suggested_equity',
        'income_gap',
        'reason',
    ])
    );
})->with('loan matcher unique records');
