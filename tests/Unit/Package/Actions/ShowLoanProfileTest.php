<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Models\LoanProfile;

uses(RefreshDatabase::class);

test('it returns a loan profile by reference code', function () {
    // Arrange: create a loan profile and attached voucher
    $loanProfile = LoanProfile::factory()->create();

    // Act: request the profile
    $response = $this->getJson(route('api.v1.loan-profiles.show', [
        'reference_code' => $loanProfile->reference_code,
    ]));

    // Assert: success and structure
    $response->assertOk()
        ->assertJsonFragment([
            'reference_code' => $loanProfile->reference_code,
        ])
        ->assertJsonStructure([
            'id',
            'reference_code',
            'lending_institution',
            'total_contract_price',
            'inputs',
            'computation',
            'qualified',
            'required_equity',
            'income_gap',
            'suggested_down_payment_percent',
            'reason',
            'reserved_at',
        ]);
});

test('it fails if reference code does not exist', function () {
    $response = $this->getJson(route('api.v1.loan-profiles.show', [
        'reference_code' => 'NONEXISTENT123',
    ]));

    $response->assertStatus(404);
});
