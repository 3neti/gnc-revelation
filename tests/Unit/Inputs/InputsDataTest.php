<?php

use App\Data\Inputs\{InputsData, IncomeInputsData, LoanableInputsData, DownPaymentInputsData, BalancePaymentInputsData, FeesInputsData, MonthlyPaymentAddOnsInputsData};
use App\Services\{BorrowingRulesService, AgeService};
use App\Classes\{Buyer, Property, Order};
use App\ValueObjects\Percent;

beforeEach(function () {
    $this->buyer = new Buyer(new BorrowingRulesService(new AgeService()));
    $this->buyer->setMonthlyGrossIncome(17_000);
    $this->buyer->setIncomeRequirementMultiplier(0.35);

    $this->property = new Property();
    $this->property->setTotalContractPrice(926_500);

    $this->order = new Order();
    $this->order
        ->setInterestRate(0.0625)
        ->setIncomeRequirementMultiplier(0.35)
        ->setPercentDownPayment(10)
        ->setPercentMiscellaneousFees(8.5)
        ->setDownPaymentTerm(12)
        ->setBalancePaymentTerm(21)
    ;
});

it('creates valid InputsData from booking', function () {
    $inputs = InputsData::fromBooking($this->buyer, $this->property, $this->order);

    expect($inputs)->toBeInstanceOf(InputsData::class)
        ->and($inputs->income)->toBeInstanceOf(IncomeInputsData::class)
        ->and($inputs->loanable)->toBeInstanceOf(LoanableInputsData::class)
        ->and($inputs->balance_payment)->toBeInstanceOf(BalancePaymentInputsData::class)
        ->and($inputs->fees)->toBeInstanceOf(FeesInputsData::class)
        ->and($inputs->monthly_payment_add_ons)->toBeInstanceOf(MonthlyPaymentAddOnsInputsData::class);
});

it('maps IncomeInputsData correctly', function () {
    $income = IncomeInputsData::fromBooking($this->buyer, $this->property, $this->order);

    expect($income->gross_monthly_income->inclusive()->getAmount()->toFloat())->toBe(17_000.0)
        ->and($income->income_requirement_multiplier->equals(Percent::ofFraction(0.35)))->toBeTrue();
});

it('maps DownPaymentInputsData correctly', function () {
    $down = DownPaymentInputsData::fromBooking($this->buyer, $this->property, $this->order);

    expect($down->percent_dp->equals(Percent::ofFraction(0.10)))->toBeTrue()
        ->and($down->dp_term)->toBe(12);
});

it('maps LoanableInputsData correctly', function () {
    $loanable = LoanableInputsData::fromBooking($this->buyer, $this->property, $this->order);

    expect($loanable->total_contract_price->inclusive()->getAmount()->toFloat())->toBe(926500.0)
        ->and($loanable->down_payment)->toBeInstanceOf(DownPaymentInputsData::class);
});

it('maps BalancePaymentInputsData correctly', function () {
    $balance = BalancePaymentInputsData::fromBooking($this->buyer, $this->property, $this->order);

    expect($balance->bp_term)->toBe(21)
        ->and($balance->bp_interest_rate->equals(Percent::ofFraction(0.0625)))->toBeTrue();
});

it('maps FeesInputsData correctly with defaults', function () {
    $fees = FeesInputsData::fromBooking($this->buyer, $this->property, $this->order);

    expect($fees->percent_mf)->toBeNull()
        ->and($fees->consulting_fee)->toBeNull()
        ->and($fees->processing_fee)->toBeNull()
    ;
});

it('maps MonthlyPaymentAddOnsInputsData correctly', function () {
    $addons = MonthlyPaymentAddOnsInputsData::fromBooking($this->buyer, $this->property, $this->order);

    expect($addons->monthly_mri)->toBe(0.0)
        ->and($addons->monthly_fi)->toBe(0.0);
});
