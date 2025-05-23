<?php

use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Classes\Property;
use LBHurtado\Mortgage\Enums\Property\{DevelopmentType};
use LBHurtado\Mortgage\Enums\Property\DevelopmentForm;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

beforeEach(function () {
    config()->set('gnc-revelation.property.default.development_type', 'bp_957');
    config()->set('gnc-revelation.property.default.development_form', 'horizontal');
    config()->set('gnc-revelation.default_buffer_margin', 0.2);

    config()->set('gnc-revelation.property.market.percent_disposable_income', [
        'open' => 0.3,
        'economic' => 0.35,
        'socialized' => 0.35,
    ]);

    config()->set('gnc-revelation.property.market.percent_loanable_value', [
        'open' => 0.90,
        'economic' => 0.95,
        'socialized' => 1.00,
    ]);
});

it('creates a property with default development type and form', function () {
    $property = new Property(1_000_000);

    expect($property->getDevelopmentType())->toBe(DevelopmentType::BP_957)
        ->and($property->getDevelopmentForm())->toBe(DevelopmentForm::HORIZONTAL);
});

it('converts total contract price from scalar to Price', function () {
    $property = new Property(2_000_000);

    expect($property->getTotalContractPrice())->toBeInstanceOf(Price::class)
        ->and($property->getTotalContractPrice()->inclusive()->getAmount()->toFloat())->toBeCloseTo(2000000);
    ;
});

it('sets and gets buffer margin as Percent', function () {
    $property = new Property(1_000_000);

    expect($property->getRequiredBufferMargin())->toEqualPercent(0.2);
});

it('computes default disposable income requirement from segment', function () {
    $property = new Property(850_000);

    expect($property->getIncomeRequirementMultiplier())->toEqualPercent(0.35);
});

it('allows setting custom disposable income requirement', function () {
    $property = new Property(1_000_000);
    $property->setIncomeRequirementMultiplier(0.4);

    expect($property->getIncomeRequirementMultiplier())->toEqualPercent(0.4);
});

it('computes default loanable value percent from segment', function () {
    $property = new Property(850_000);

    expect($property->getPercentLoanableValue())->toEqualPercent(1.00);
});

it('allows overriding loanable value percent', function () {
    $property = new Property(1_000_000);
    $property->setPercentLoanableValue(0.80);

    expect($property->getPercentLoanableValue())->toEqualPercent(0.80);
});

it('computes loanable amount from appraisal value if set', function () {
    $property = new Property(1_000_000);
    $property->setAppraisalValue(1_200_000);
    $property->setPercentLoanableValue(0.90);

    expect($property->getLoanableAmount()->inclusive()->getAmount()->toFloat())->toBeCloseTo(1_080_000);
});

it('computes loanable amount from TCP if no appraisal', function () {
    $property = new Property(1_000_000);
    $property->setPercentLoanableValue(0.85);

    expect($property->getLoanableAmount()->inclusive()->getAmount()->toFloat())->toBeCloseTo(850_000);
});

it('sets and gets processing fee', function () {
    $property = new Property(1_000_000);
    $property->setProcessingFee(10_000);

    expect($property->getProcessingFee())->toBeInstanceOf(Price::class)
        ->and($property->getProcessingFee()->inclusive()->getAmount()->toFloat())->toBeCloseTo(10_000);
});

it('sets and gets miscellaneous fees as Percent', function () {
    $property = new Property(1_000_000);
    $property->setPercentMiscellaneousFees(0.085);

    expect($property->getPercentMiscellaneousFees())->toEqualPercent(0.085);
});

dataset('default_interest_rates', function () {
    return [
        'SOCIALIZED @ ₱750k'    => [750_000,   0.03  ],
        'SOCIALIZED @ ₱850k'    => [850_000,   0.0625],
        'ECONOMIC   @ ₱1M'      => [1_000_000, 0.0625],
        'OPEN       @ ₱3M'      => [3_000_000, 0.07  ],
        'OPEN       @ ₱5M'      => [5_000_000, 0.07  ],
    ];
});

it('uses correct default interest rate based on TCP', function (float $tcp, float $expectedRate) {
    $property = new Property($tcp);

    expect($property->getInterestRate())->toEqualPercent($expectedRate);
})->with('default_interest_rates');

it('allows overriding interest rate', function () {
    $property = new Property(1_000_000);
    $property->setInterestRate(0.065);

    expect($property->getInterestRate())->toEqualPercent(0.065);
});

it('defaults down payment to 0 percent if not set', function () {
    $property = new Property(1_000_000);
    $default = config('gnc-revelation.property.default.percent_dp');
    expect($default)->toBe(0.0)
        ->and($property->getPercentDownPayment()->value())->toBe($default);

});

it('defaults down payment to lending institution default if set', function () {
    $property = new Property(1_000_000);
    $lendingInstitution = new LendingInstitution('rcbc');
    $property->setLendingInstitution($lendingInstitution);
    $default = $lendingInstitution->getPercentDownPayment()->value();
    expect($default)->toBe(0.10)
        ->and($property->getPercentDownPayment()->value())->toBe($default);
});

it('accepts a numeric percent (e.g., 10 for 10%)', function () {
    $property = new Property(1_000_000);
    $property->setPercentDownPayment(10);

    expect($property->getPercentDownPayment()->value())->toBe(0.10);
});

it('accepts a fraction (e.g., 0.10 for 10%)', function () {
    $property = new Property(1_000_000);
    $property->setPercentDownPayment(0.10);

    expect($property->getPercentDownPayment())->toEqualPercent(0.10);
});

it('accepts a Percent instance', function () {
    $property = new Property(1_000_000);
    $property->setPercentDownPayment(Percent::ofPercent(15));

    expect($property->getPercentDownPayment()->value())->toBe(0.15);
});

it('throws exception for negative numeric down payment', function () {
    $property = new Property(1_000_000);

    expect(fn () => $property->setPercentDownPayment(-5))
        ->toThrow(\InvalidArgumentException::class, 'Down payment percent must not be negative.');
});

it('throws exception for negative Percent object', function () {
    $property = new Property(1_000_000);
    $negative = -15;
    $property->setPercentDownPayment($negative);
})->throws(InvalidArgumentException::class);

it('throws exception for unsupported type', function () {
    $property = new Property(1_000_000);
    $property->setPercentDownPayment('ten percent');
})->throws(TypeError::class);
