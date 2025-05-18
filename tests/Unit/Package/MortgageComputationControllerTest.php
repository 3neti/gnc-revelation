<?php

use Illuminate\Testing\Fluent\AssertableJson;

it('computes mortgage result and qualification correctly', function () {
    $response = $this->postJson(route('api.v1.mortgage-compute'), [
        'lending_institution' => 'hdmf',
        'total_contract_price' => 1_000_000,
        'buyer' => [
            'age' => 49,
            'monthly_income' => 17_000,
            'additional_income' => 0,
        ],
        'co_borrower' => null,
        'percent_down_payment' => null,
        'percent_miscellaneous_fee' => 0.00,
        'processing_fee' => 0,
        'add_mri' => false,
        'add_fi' => false,
    ]);

    $response->assertOk();

    $response->assertJson(fn (AssertableJson $json) =>
    $json->hasAll([
        'payload.inputs',
//        'payload.term_years',
        'payload.monthly_amortization',
        'payload.cash_out',
//        'payload.loanable_amount',
        'qualification.qualifies',
        'qualification.income_gap',
        'qualification.loan_difference',
        'qualification.suggested_down_payment_percent',
        'qualification.reason',
        'qualification.mortgage.monthly_amortization',
        'qualification.mortgage.term_years',
    ])
//        ->where('payload.inputs.total_contract_price', 1_000_000)
//        ->where('payload.inputs.gross_monthly_income', 17_000)
//        ->where('payload.term_years', 21)
        ->where('qualification.qualifies', false)
    );
})->skip();
