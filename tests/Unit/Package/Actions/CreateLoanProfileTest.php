
<?php

use LBHurtado\Mortgage\Data\Inputs\MortgageInputsData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Actions\CreateLoanProfile;
use LBHurtado\Mortgage\Models\LoanProfile;

uses(RefreshDatabase::class);

test('create loan profile stores computed result and inputs', function () {
    $input = MortgageInputsData::from([
        'lending_institution' => 'hdmf',
        'total_contract_price' => 1_000_000,
        'age' => 45,
        'monthly_gross_income' => 25_000,
        'co_borrower_age' => 0,
        'co_borrower_income' => 25_000,
        'additional_income' => 0,
        'balance_payment_interest' => null,
        'percent_down_payment' => null,
        'percent_miscellaneous_fee' => 0.0,
        'processing_fee' => 0,
        'add_mri' => false,
        'add_fi' => false,
    ]);

    $loanProfile = CreateLoanProfile::run($input);

    expect($loanProfile)->toBeInstanceOf(LoanProfile::class)
        ->and($loanProfile->reference_code)->toBeString()
        ->and($loanProfile->qualified)->toBeBool()
        ->and($loanProfile->inputs)->toBeArray()
        ->and($loanProfile->computation)->toBeArray()
        ->and($loanProfile->total_contract_price)->toBeFloat();
});

test('loan profile endpoint stores and returns profile', function () {
    $payload = [
        'lending_institution' => 'hdmf',
        'total_contract_price' => 1_000_000,
        'age' => 45,
        'monthly_gross_income' => 25_000,
        'co_borrower_age' => 0,
        'co_borrower_income' => 25_000,
        'additional_income' => 0,
        'balance_payment_interest' => null,
        'percent_down_payment' => null,
        'percent_miscellaneous_fee' => 0.0,
        'processing_fee' => 0,
        'add_mri' => false,
        'add_fi' => false,
    ];

    $response = $this->postJson(route('api.v1.loan-profiles.store'), $payload);

    $response->assertOk();
    $response->assertJsonFragment([
        'lending_institution' => 'hdmf',
        'total_contract_price' => 1000000,
    ]);

    $referenceCode = $response->json('reference_code');
    expect($referenceCode)->toBeString()->not->toBeEmpty();

    $this->assertDatabaseHas('loan_profiles', [
        'reference_code' => $referenceCode,
        'lending_institution' => 'hdmf',
        'total_contract_price' => 1000000,
    ]);
});
