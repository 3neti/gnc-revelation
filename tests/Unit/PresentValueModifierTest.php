<?php

use App\Modifiers\PresentValueModifier;
use Brick\Money\Money;
use Whitecube\Price\Price;

describe('PresentValueModifier', function () {

    it('computes present value correctly for non-zero interest', function () {
        $monthly = Money::of(7000, 'PHP');
        $price = new Price($monthly);

        $modifier = new PresentValueModifier(
            bpTermYears: 20,
            bpInterestRateAnnual: 0.06 // 6% annually
        );

        $price->addModifier('present value', $modifier);

        $presentValue = $price->exclusive()->getAmount()->toFloat();

        // Expected value using annuity formula
        $months = 20 * 12;
        $rate = 0.06 / 12;
        $expected = 7000 * (1 - pow(1 + $rate, -$months)) / $rate;

        expect($presentValue)->toBeCloseTo($expected, 0.01);
    });

    it('computes present value correctly for zero interest', function () {
        $monthly = Money::of(7000, 'PHP');
        $price = new Price($monthly);

        $modifier = new PresentValueModifier(
            bpTermYears: 20,
            bpInterestRateAnnual: 0.00
        );

        $price->addModifier('present value', $modifier);

        $presentValue = $price->exclusive()->getAmount()->toFloat();

        $expected = 7000 * 240; // 20 years * 12 months

        expect($presentValue)->toEqual($expected);
    });

    it('exposes correct attributes', function () {
        $modifier = new PresentValueModifier(
            bpTermYears: 15,
            bpInterestRateAnnual: 0.045 // 4.5%
        );

        expect($modifier->attributes())->toMatchArray([
            'bp_term_months' => 180,
            'monthly_interest_rate' => round(0.045 / 12, 15),
        ]);
    });

});
