<?php

use LBHurtado\Mortgage\Traits\HasFinancialAttributes;
use LBHurtado\Mortgage\ValueObjects\Percent;

beforeEach(function () {
    $this->instance = new class {
        use HasFinancialAttributes;
    };
});

it('sets and gets interest rate correctly', function () {
    $this->instance->setInterestRate(6.25);
    expect($this->instance->getInterestRate()->asPercent())->toBe(6.25);

    $this->instance->setInterestRate(0.08);
    expect($this->instance->getInterestRate()->value())->toBe(0.08);

    $this->instance->setInterestRate(7);
    expect($this->instance->getInterestRate()->equals(Percent::ofPercent(7.0)))->toBeTrue();
});

it('throws TypeError when trying to set null interest rate', function () {
    $this->instance->setInterestRate(null);
})->throws(TypeError::class);

it('throws when setting invalid interest rate', function () {
    $this->instance->setInterestRate("invalid");
})->throws(TypeError::class);

it('throws LogicException if no interest rate is set and resolveDefaultInterestRate is not implemented', function () {
    $this->instance->getInterestRate();
})->throws(LogicException::class, 'The class using HasFinancialAttributes must implement resolveDefaultInterestRate() or set an interest rate.');

it('sets and gets income requirement multiplier correctly', function () {
    $this->instance->setIncomeRequirementMultiplier(0.35);
    expect($this->instance->getIncomeRequirementMultiplier()->value())->toBe(0.35);

    $this->instance->setIncomeRequirementMultiplier(35);
    expect($this->instance->getIncomeRequirementMultiplier()->asPercent())->toBe(35.0);

    $this->instance->setIncomeRequirementMultiplier(Percent::ofFraction(0.4));
    expect($this->instance->getIncomeRequirementMultiplier()->value())->toBe(0.4);

    $this->instance->setIncomeRequirementMultiplier(null);
    expect($this->instance->getIncomeRequirementMultiplier())->toBeNull();
});

it('throws when setting invalid income requirement multiplier', function () {
    $this->instance->setIncomeRequirementMultiplier(['oops']);
})->throws(TypeError::class);

it('sets and gets percent miscellaneous fees correctly', function () {
    $this->instance->setPercentMiscellaneousFees(5);
    expect($this->instance->getPercentMiscellaneousFees()->asPercent())->toBe(5.0);

    $this->instance->setPercentMiscellaneousFees(0.05);
    expect($this->instance->getPercentMiscellaneousFees()->value())->toBe(0.05);

    $this->instance->setPercentMiscellaneousFees(Percent::ofPercent(1.5));
    expect($this->instance->getPercentMiscellaneousFees()->asPercent())->toBe(1.5);

    $this->instance->setPercentMiscellaneousFees(null);
    expect($this->instance->getPercentMiscellaneousFees())->toBeNull();
});

it('throws when setting invalid miscellaneous fees', function () {
    $this->instance->setPercentMiscellaneousFees(new stdClass());
})->throws(TypeError::class);
