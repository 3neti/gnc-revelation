<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Mortgage\Models\LoanProfile;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

test('onboard loan profile returns onboarding URL with reference code', function () {
    // Arrange: Fake onboarding URL and loan profile
    Config::set('gnc-revelation.onboarding.url', 'https://onboard.test/kyc');

    $loanProfile = LoanProfile::factory()->create();
    $field_name = config('gnc-revelation.onboarding.field_name');

    // Act: Call the endpoint
    $response = $this->getJson(route('api.v1.loan-profiles.onboard', [
        'reference_code' => $loanProfile->reference_code,
    ]));

    // Assert: Check URL format and 200 response
    $response->assertOk()
        ->assertJson([
            'url' => "https://onboard.test/kyc?{$field_name}={$loanProfile->reference_code}",
        ]);
});

test('onboard loan profile fails if reference code is invalid', function () {
    // Arrange: Fake onboarding URL
    Config::set('gnc-revelation.onboarding.url', 'https://onboard.test/kyc');

    // Act: Call with invalid code
    $response = $this->getJson(route('api.v1.loan-profiles.onboard', [
        'reference_code' => 'INVALID-CODE-123',
    ]));

    // Assert: Should return 404
    $response->assertNotFound();
});
