<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Models\LoanProfile;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class, WithFaker::class);

test('loan profile has attributes', function () {
    $loan_profile = LoanProfile::factory()->create();
    expect($loan_profile)->toBeInstanceOf(LoanProfile::class);
    expect($loan_profile->id)->toBeString();
    expect($loan_profile->reference_code)->toBeString();
    expect($loan_profile->lending_institution)->toBeString();
    expect($loan_profile->total_contract_price)->toBeFloat();
    expect($loan_profile->inputs)->toBeArray();
    expect($loan_profile->computation)->toBeArray();
    expect($loan_profile->qualified)->toBeBool();
    expect($loan_profile->required_equity)->toBeFloat();
    expect($loan_profile->income_gap)->toBeFloat();
    expect($loan_profile->suggested_down_payment_percent)->toBeFloat();
    expect($loan_profile->reason)->toBeString();
    expect($loan_profile->reserved_at)->toBeInstanceOf(Carbon::class);
});

test('loan profile has vouchers', function () {
    $loan_profile = LoanProfile::factory()->create();
    $reference_code = $loan_profile->reference_code;
    $voucher = Voucher::where('code', $reference_code)->first();
    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($voucher->voucherEntities)->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
    expect($voucher->getEntities(LoanProfile::class)->first()->is($loan_profile))->toBeTrue();
});

test('loan profile can be persisted', function () {
    $loan_profile = app(LoanProfile::class)->create([
        'lending_institution' => $this->faker->randomElement(LendingInstitution::keys()),
        'total_contract_price' => $this->faker->numberBetween(800_000, 4_000_000),
        'inputs' => $this->faker->rgbColorAsArray(),
        'computation' => $this->faker->rgbColorAsArray(),
        'qualified' => true,
        'required_equity' => 100000,
        'income_gap' => 100000,
        'suggested_down_payment_percent' => '0.13',
        'reason' => $this->faker->sentence,
        'reserved_at' => $this->faker->dateTime,
    ]);
    expect($loan_profile)->toBeInstanceOf(LoanProfile::class);
});
