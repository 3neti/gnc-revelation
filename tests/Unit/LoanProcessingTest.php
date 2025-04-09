<?php

use App\Services\{LoanProcessingService, MortgageComputation};
use App\Data\{
    Inputs\InputsData,
    Inputs\IncomeInputsData,
    Inputs\LoanableInputsData,
    Inputs\DownPaymentInputsData,
    Inputs\BalancePaymentInputsData,
    Inputs\MonthlyPaymentAddOnsInputsData,
    LoanProcessingData,
    ProductMatchData,
    RemediationStrategiesData,
    CashOutScheduleData,
    BalancePaymentScheduleData,
    QualificationResultData
};
use App\Support\MoneyFactory;
use App\Classes\{Buyer, Property, Order};
use App\Services\BorrowingRulesService;
use App\Services\AgeService;
use Tests\Fakes\FlexibleFakeBuyer;
use Tests\Fakes\FlexibleFakeOrder;
use Tests\Fakes\FlexibleFakeProperty;
use Whitecube\Price\Price;

use App\ValueObjects\Percent;

it('generates the loan processing result correctly', function () {
    $rules = new BorrowingRulesService(new AgeService());

    $tcp = 926_500;
    $down_payment_percent = Percent::ofPercent(10); // use the Percent VO

    $buyer = new Buyer($rules);
    $buyer->setMonthlyGrossIncome(MoneyFactory::price(17_000));
    $buyer->setIncomeRequirementMultiplier(0.35);

    $property = new Property();
    $property->setTotalContractPrice($tcp);

    $order = new Order();
    $order->setInterestRate(0.0625)
        ->setPercentDownPayment($down_payment_percent)
        ->setIncomeRequirementMultiplier(0.35)
        ->setDownPaymentTerm(12)
        ->setBalancePaymentTerm(21)
        ->setMonthlyMRI(0)
        ->setMonthlyFI(0);

    $service = new LoanProcessingService(buyer: $buyer, property: $property, order: $order);
    $result = $service->generate();
    $qualification = $result->qualification;

    dump([
        'expected_actual_dp' => $tcp * $down_payment_percent->value(),
        'actual_down_payment' => $qualification->actual_down_payment,
        'order_percent_dp' => $order->getPercentDownPayment(),
    ]);

    expect($result)->toBeInstanceOf(LoanProcessingData::class)
        ->and($qualification)->toBeInstanceOf(QualificationResultData::class)
        ->and($qualification->qualifies)->toBeTrue()
        ->and($qualification->affordable_loanable)->toBeCloseTo(833_878.13, 0.01)
        ->and($result->cash_out_schedule->monthly_dp_payment)->toBeInstanceOf(Price::class)
        ->and($result->balance_payment_schedule->monthly_amortization)->toBeInstanceOf(Price::class)
        ->and($result->product_match)->toBeInstanceOf(ProductMatchData::class)
        ->and($result->remediation)->toBeInstanceOf(RemediationStrategiesData::class);
});

