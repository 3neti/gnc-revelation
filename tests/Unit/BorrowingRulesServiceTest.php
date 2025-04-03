<?php

use App\Exceptions\MaximumBorrowingAgeBreached;
use App\Exceptions\MinimumBorrowingAgeNotMet;
use App\Services\BorrowingRulesService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->rules = app(BorrowingRulesService::class);
});

it('can calculate age in float', function () {
    $birthdate = now()->subYears(30)->subDays(180); // ~30.5
    $age = $this->rules->calculateAge($birthdate);

    expect($age)->toBeFloat()
        ->and($age)->toBeGreaterThan(30);
});

it('throws exception if age is below minimum', function () {
    $tooYoung = now()->subYears($this->rules->getMinimumAge() - 1);
    $this->rules->validateBirthdate($tooYoung); // Exception expected
})->throws(MinimumBorrowingAgeNotMet::class);

it('throws exception if age exceeds maximum', function () {
    $tooOld = now()->subYears($this->rules->getMaximumAge() + 1);
    $this->rules->validateBirthdate($tooOld);
})->throws(MaximumBorrowingAgeBreached::class);
