<?php

use App\Modifiers\PeriodicPaymentModifier;
use Whitecube\Price\Price;
use Brick\Money\Money;

it('computes periodic payment with zero interest', function () {
    $amount = Money::of(120_000, 'PHP'); // ₱120,000 loan
    $price = new Price($amount);

    $modifier = new PeriodicPaymentModifier(
        termInMonths: 12,       // 1 year
        monthlyRate: 0.0        // 0% interest
    );

    $result = $modifier->apply($amount, 1, false);

    expect($result->getAmount()->toFloat())->toBe(10_000.0) // ₱10,000/month
    ->and($result->getCurrency()->getCurrencyCode())->toBe('PHP');
});

it('computes periodic payment with interest', function () {
    $amount = Money::of(500_000, 'PHP'); // ₱500,000 loan
    $price = new Price($amount);

    $modifier = new PeriodicPaymentModifier(
        termInMonths: 24,              // 2 years
        monthlyRate: 0.00520833        // ~6.25% annual interest
    );

    $result = $modifier->apply($amount, 1, false);

    // This should match amortization formula
    $expected = (500_000 * 0.00520833) / (1 - pow(1 + 0.00520833, -24));

    expect($result->getAmount()->toFloat())
        ->toBeCloseTo($expected, 0.01);
});

it('modifies a Price using PeriodicPaymentModifier', function () {
    $base = new Price(Money::of(240_000, 'PHP'));

    $price = $base->addModifier('periodic', PeriodicPaymentModifier::class, 24, 0.00520833);

    expect($price->inclusive()->getAmount()->toFloat())
        ->toBeCloseTo((240_000 * 0.00520833) / (1 - pow(1 + 0.00520833, -24)), 0.01);
});
