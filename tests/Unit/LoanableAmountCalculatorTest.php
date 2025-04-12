<?php

use App\Classes\{Buyer, Property, Order};
use App\Factories\CalculatorFactory;
use App\Data\Inputs\InputsData;
use App\Enums\CalculatorType;
use App\ValueObjects\Percent;

dataset('loanable amounts with dp only', [
    '1M TCP with 10% DP' => [1_000_000, 0.10, 900_000.00],
    '1.2M TCP with 15% DP' => [1_200_000, 0.15, 1_020_000.00],
    '900k TCP with 0% DP' => [900_000, 0.00, 900_000.00],
    '2.5M TCP with 20% DP' => [2_500_000, 0.20, 2_000_000.00],
    '750k TCP with 5% DP' => [750_000, 0.05, 712_500.00],
]);

it('computes loanable amount using down payment deduction only', function (
    float $tcp,
    float $dpPercent,
    float $expectedLoanable
) {
    $buyer = app(Buyer::class); // You can set more buyer values if needed
    $property = new Property($tcp);
    $order = (new Order())
        ->setInterestRate(Percent::ofFraction(0.0625))
        ->setPercentDownPayment($dpPercent)
    ;
    $inputs = InputsData::fromBooking($buyer, $property, $order);

    $loanable = CalculatorFactory::make(CalculatorType::LOANABLE_AMOUNT, $inputs)
        ->calculate()
        ->getAmount()
        ->toFloat();

    expect($loanable)->toBeCloseTo($expectedLoanable, 0.01);
})->with('loanable amounts with dp only');
