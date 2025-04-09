<?php

use App\ValueObjects\Percent;

it('creates from percent correctly', function () {
    $p = Percent::ofPercent(10);
    expect($p->value())->toBe(0.10)
        ->and($p->asPercent())->toBe(10.0)
        ->and((string)$p)->toBe('10.00%');
});

it('creates from fraction correctly', function () {
    $p = Percent::ofFraction(0.35);
    expect($p->value())->toBe(0.35)
        ->and($p->asPercent())->toBe(35.0)
        ->and((string)$p)->toBe('35.00%');
});

it('throws for percent above 100', function () {
    Percent::ofPercent(120);
})->throws(InvalidArgumentException::class, 'Percent must be between 0 and 100.');

it('throws for fraction above 1', function () {
    Percent::ofFraction(1.5);
})->throws(InvalidArgumentException::class, 'Fraction must be between 0 and 1.');

it('throws for negative percent', function () {
    Percent::ofPercent(-5);
})->throws(InvalidArgumentException::class, 'Percent must be between 0 and 100.');

it('throws for negative fraction', function () {
    Percent::ofFraction(-0.01);
})->throws(InvalidArgumentException::class, 'Fraction must be between 0 and 1.');

it('creates percent from fraction and percent', function () {
    expect(Percent::ofFraction(0.10)->asPercent())->toBe(10.0)
        ->and((string) Percent::ofPercent(25))->toBe('25.00%');
});

it('compares percent values correctly', function () {
    $a = Percent::ofPercent(10);
    $b = Percent::ofFraction(0.10);
    $c = Percent::ofPercent(15);

    expect($a->equals($b))->toBeTrue()
        ->and($a->lessThan($c))->toBeTrue()
        ->and($c->greaterThan($b))->toBeTrue();
});

it('supports basic arithmetic', function () {
    $a = Percent::ofPercent(20);
    $b = Percent::ofPercent(5);

    expect($a->multiply(1000))->toBe(200.0)
        ->and($a->add($b)->asPercent())->toBeCloseTo(25.0, 0.00001)
        ->and($a->subtract($b)->asPercent())->toBeCloseTo(15.0, 0.00001);
});

it('supports comparison methods', function () {
    $a = Percent::ofPercent(25);
    $b = Percent::ofFraction(0.25); // 25%
    $c = Percent::ofPercent(10);

    expect($a->equals($b))->toBeTrue()
        ->and($a->greaterThan($c))->toBeTrue()
        ->and($c->lessThan($a))->toBeTrue()
        ->and($a->lessThan($b))->toBeFalse()
        ->and($a->greaterThan($b))->toBeFalse();
});

it('treats nearly equal values correctly with equals()', function () {
    $a = Percent::ofFraction(0.30000000000000004); // float edge case
    $b = Percent::ofPercent(30);

    expect($a->equals($b))->toBeTrue(); // Uses tolerance internally
});
