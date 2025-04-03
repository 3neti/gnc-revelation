<?php

use App\Services\AgeService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->service = new AgeService();
});

it('calculates integer age correctly', function () {
    $birthdate = now()->subYears(40)->startOfDay();
    $age = $this->service->getAgeInYears($birthdate);

    expect($age)->toBe(40);
});

it('calculates float age correctly', function () {
    $birthdate = now()->subYears(40)->subDays(182); // ~40.5
    $age = $this->service->getAgeInFloat($birthdate);

    expect($age)->toBeGreaterThan(40)
        ->and($age)->toBeFloat();
});

it('returns years until target age', function () {
    $birthdate = now()->subYears(30);
    $yearsLeft = $this->service->getYearsUntilAge($birthdate, 60);

    expect($yearsLeft)->toBe(30);
});

it('detects if term respects paying age limit', function () {
    $birthdate = now()->subYears(45);
    $term = 20;
    $limit = 70;

    $valid = $this->service->willReachAgeWithinTerm($birthdate, $limit, $term);

    expect($valid)->toBeTrue();
});

it('fails if term exceeds paying age limit', function () {
    $birthdate = now()->subYears(55);
    $term = 20;
    $limit = 70;

    $valid = $this->service->willReachAgeWithinTerm($birthdate, $limit, $term);

    expect($valid)->toBeFalse();
});
