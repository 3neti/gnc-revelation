<?php

use LBHurtado\Mortgage\Http\Controllers\LoanMatchController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Database\Seeders\PropertySeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PropertySeeder::class); // Seeds ~8 property records from 750k to 4M
    Route::post('/loan-match', LoanMatchController::class)->name('api.v1.loan-match');
});

dataset('loan matcher buyer scenarios', [
    'Single buyer qualifies for 30k income' => [
        'age' => 30,
        'income' => 30000,
        'additional_income' => null,
        'co_borrower' => null,
        'expected_min_count' => 1
    ],
    'Single buyer does not qualify for 10k income' => [
        'age' => 40,
        'income' => 10000,
        'additional_income' => null,
        'co_borrower' => null,
        'expected_min_count' => 0
    ],
    'Buyer with co-borrower qualifies' => [
        'age' => 40,
        'income' => 12000,
        'additional_income' => null,
        'co_borrower' => ['age' => 35, 'monthly_income' => 15000],
        'expected_min_count' => 1
    ],
    'Buyer with additional income qualifies' => [
        'age' => 45,
        'income' => 15000,
        'additional_income' => 10000,
        'co_borrower' => null,
        'expected_min_count' => 1
    ],
]);

it('returns qualified properties based on income and age', function (
    int $age,
    float $income,
    ?float $additional_income,
    ?array $co_borrower,
    int $expected_min_count
) {
    $payload = [
        'age' => $age,
        'monthly_income' => $income,
    ];

    if ($additional_income !== null) {
        $payload['additional_income'] = $additional_income;
    }

    if ($co_borrower !== null) {
        $payload['co_borrower'] = $co_borrower;
    }

    $response = $this->postJson(route('api.v1.loan-match'), $payload);

    $response->assertOk();
    $json = $response->json();

    expect($json)->toBeArray();
    expect(collect($json)->count())->toBeGreaterThanOrEqual($expected_min_count);

    if ($expected_min_count > 0) {
        expect(collect($json)->every(fn ($item) => $item['qualified'] === true))->toBeTrue();
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
    }
})->with('loan matcher buyer scenarios');
