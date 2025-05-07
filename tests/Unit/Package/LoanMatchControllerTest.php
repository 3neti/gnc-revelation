<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Facades\Route;
use LBHurtado\Mortgage\Http\Controllers\LoanMatchController;

beforeEach(function () {
    Route::post('/loan-match', LoanMatchController::class); // Bind route for test
});

it('returns only qualified loan products', function () {
    $payload = [
        'age' => 30,
        'monthly_income' => 30000,
        'products' => [
            [
                'code' => 'P750',
                'name' => 'RDG750',
                'tcp' => 750000,
                'interest_rate' => 0.0625,
                'max_term_years' => 20,
                'max_loanable_percent' => 1.0,
                'disposable_income_multiplier' => 0.35,
            ],
            [
                'code' => 'P2000',
                'name' => 'RDG2000',
                'tcp' => 2000000,
                'interest_rate' => 0.0625,
                'max_term_years' => 30,
                'max_loanable_percent' => 1.0,
                'disposable_income_multiplier' => 0.35,
            ],
        ],
    ];

    $response = $this->postJson(route('api.v1.loan-match'), $payload);

    $response->assertOk()
        ->assertJsonCount(1) // Only P750 qualifies
        ->assertJson(fn (AssertableJson $json) =>
        $json->first(fn ($json) =>
        $json->where('product_code', 'P750')
            ->where('qualified', true)
            ->hasAll([
                'monthly_amortization',
                'income_required',
                'suggested_equity',
                'income_gap',
                'reason',
            ])
        )
        );
});
