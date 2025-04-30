<?php

use App\Modifiers\DownPaymentDeductionModifier;
use Brick\Money\Money;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;
use Whitecube\Price\PriceManager;

dataset('down payment deduction', [
    '10% of 1,000,000' => [1_000_000, 0.10, 900_000.00],
    '15% of 1,200,000' => [1_200_000, 0.15, 1_020_000.00],
    '0% of 900,000'    => [900_000,   0.00, 900_000.00],
    '20% of 2,500,000' => [2_500_000, 0.20, 2_000_000.00],
    '5% of 750,000'    => [750_000,   0.05, 712_500.00],
]);

it('applies down payment deduction correctly', function (
    float $tcp,
    float $dpRate,
    float $expected
) {
    $modifier = new DownPaymentDeductionModifier(Percent::ofFraction($dpRate));
    $price = new Price(Money::of($tcp, 'PHP'));
    $price->addModifier('down_payment', $modifier);

    expect($price->inclusive()->getAmount()->toFloat())->toBeCloseTo($expected, 0.01);
})->with('down payment deduction');
