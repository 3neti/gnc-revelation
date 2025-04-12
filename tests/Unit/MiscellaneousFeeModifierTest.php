<?php

use App\Modifiers\MiscellaneousFeeModifier;
use App\ValueObjects\Percent;
use Whitecube\Price\Price;
use Whitecube\Price\Vat;
use Brick\Money\Money;

dataset('miscellaneous fee modifier', [
    '8.5% MF on 1M'   => [1_000_000, 0.085, 1_085_000.00],
    '10% MF on 500k'  => [500_000, 0.10, 550_000.00],
    '5% MF on 2M'     => [2_000_000, 0.05, 2_100_000.00],
    '0% MF on 800k'   => [800_000, 0.00, 800_000.00],
    '12.75% MF on 1.5M' => [1_500_000, 0.1275, 1_691_250.00],
]);

it('applies miscellaneous fee as additive percent', function (
    float $tcp,
    float $mfPercent,
    float $expectedTotal
) {
    $price = new Price(Money::of($tcp, 'PHP'));
    $modifier = new MiscellaneousFeeModifier(
        mfPercent: Percent::ofFraction($mfPercent)
    );

    $adjusted = $modifier->apply(
        $price->exclusive(),
        units: 1,
        perUnit: false,
        exclusive: $price->exclusive()
    );

    expect($adjusted->getAmount()->toFloat())
        ->toBeCloseTo($expectedTotal, 0.01);
})->with('miscellaneous fee modifier');
