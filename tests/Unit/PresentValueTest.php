<?php

use App\Services\Mortgage\PresentValue;
use App\DataObjects\MortgageTerm;
use Whitecube\Price\Price;

dataset('present value cases', function () {
    return [
        'standard case 1' => [10500, 0.06, 30, 1_751_311.95],
        'standard case 2' => [12250, 0.06, 30, 2_043_197.28],
        'edge of limit case' => [11991.01, 0.06, 30, 1_999_999.92],
    ];
});

it('calculates the correct present value from payment streams', function (
    float $monthlyPayment,
    float $annualInterestRate,
    int $termInYears,
    float $expectedPresentValue
) {
    $pv = (new PresentValue)
        ->setPayment($monthlyPayment)
        ->setInterestRate($annualInterestRate)
        ->setTerm(new MortgageTerm($termInYears));

    $discounted = $pv->getDiscountedValue();

    expect($discounted)->toBeInstanceOf(Price::class)
        ->and($discounted->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo($expectedPresentValue, 1.0);
})->with('present value cases');
