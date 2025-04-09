<?php

use App\Enums\Property\{DevelopmentType, DevelopmentForm, MarketSegment};
use App\Support\MoneyFactory;
use Brick\Money\Money;

beforeEach(function () {
    config()->set('gnc-revelation.property.market.ceiling', [
        'bp_957' => [
            'horizontal' => [
                'socialized' => 850_000,
                'economic' => 2_500_000,
                'open' => 10_000_000,
            ],
            'vertical' => [
                'socialized' => 1_500_000,
                'economic' => 3_000_000,
                'open' => 10_000_000,
            ],
        ],
        'bp_220' => [
            'horizontal' => [
                'socialized' => 850_000,
                'economic' => 2_500_000,
                'open' => 10_000_000,
            ],
            'vertical' => [
                'socialized' => 1_800_000,
                'economic' => 2_500_000,
                'open' => 10_000_000,
            ],
        ],
    ]);

    config()->set('gnc-revelation.property.market.segment', [
        'open' => 'Open Market',
        'economic' => 'Economic',
        'socialized' => 'Socialized',
    ]);

    config()->set('gnc-revelation.property.market.percent_disposable_income', [ // ✅ fixed key
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

it('supports Money and Price input for fromPrice()', function () {
    $money = Money::of(800_000, 'PHP');
    $price = MoneyFactory::price(800_000);

    expect(MarketSegment::fromPrice($money, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL))->toBe(MarketSegment::SOCIALIZED)
        ->and(MarketSegment::fromPrice($price, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL))->toBe(MarketSegment::SOCIALIZED)
    ;
});

it('returns correct human-readable name', function () {
    expect(MarketSegment::OPEN->getName())->toBe('Open Market')
        ->and(MarketSegment::ECONOMIC->getName())->toBe('Economic')
        ->and(MarketSegment::SOCIALIZED->getName())->toBe('Socialized');
});

it('returns correct default multipliers', function () {
    expect(MarketSegment::OPEN->defaultPercentLoanableValue())->toEqualPercent(0.90)
        ->and(MarketSegment::ECONOMIC->defaultPercentDisposableIncomeRequirement())->toEqualPercent(0.35);
});

it('returns default MarketSegment', function () {
    expect(MarketSegment::default())->toBe(MarketSegment::SOCIALIZED);
});

it('returns correct options structure', function () {
    $options = MarketSegment::options();

    expect($options)->toHaveCount(3)
        ->and($options[0])->toHaveKeys(['value', 'label']);

    foreach ($options as $opt) {
        expect($opt['value'])->toBeString()
            ->and($opt['label'])->toBeString();
    }
});

it('throws if market segment config is incomplete', function () {
    config()->set('gnc-revelation.property.market.ceiling.bp_957.horizontal', null);

    MarketSegment::fromPrice(850_000, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL);
})->throws(RuntimeException::class, 'Market segment ceilings are not properly configured');

it('has strictly increasing thresholds per form', function () {
    foreach (['bp_957', 'bp_220'] as $dev) {
        foreach (['horizontal', 'vertical'] as $form) {
            $ceil = config("gnc-revelation.property.market.ceiling.{$dev}.{$form}");
            expect($ceil['socialized'])->toBeLessThan($ceil['economic'])
                ->and($ceil['economic'])->toBeLessThan($ceil['open']);
        }
    }
});

it('throws if ceilings are missing or non-numeric', function () {
    config()->set('gnc-revelation.property.market.ceiling.bp_957.horizontal', [
        'socialized' => null,
        'economic' => 'not-a-number',
        'open' => [],
    ]);

    MarketSegment::fromPrice(850_000, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL);
})->throws(RuntimeException::class, 'Ceilings must include numeric values for socialized, economic, and open.');

dataset('market_segments', function () {
    return [
        // BP_957 → HORIZONTAL
        'BP_957 + HORIZONTAL → SOCIALIZED @ ₱850k'     => [850_000, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL, MarketSegment::SOCIALIZED, 1.00, 0.35],
        'BP_957 + HORIZONTAL → ECONOMIC @ ₱850,001'    => [850_001, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_957 + HORIZONTAL → ECONOMIC @ ₱2.5M'       => [2_500_000, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_957 + HORIZONTAL → OPEN @ ₱2.5M+1'         => [2_500_001, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL, MarketSegment::OPEN, 0.90, 0.30],
        'BP_957 + HORIZONTAL → OPEN @ ₱5M'             => [5_000_000, DevelopmentType::BP_957, DevelopmentForm::HORIZONTAL, MarketSegment::OPEN, 0.90, 0.30],

        // BP_957 → VERTICAL
        'BP_957 + VERTICAL → ECONOMIC @ ₱1.8M'          => [1_800_000, DevelopmentType::BP_957, DevelopmentForm::VERTICAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_957 + VERTICAL → ECONOMIC @ ₱1.8M+1'        => [1_800_001, DevelopmentType::BP_957, DevelopmentForm::VERTICAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_957 + VERTICAL → ECONOMIC @ ₱2.5M'          => [2_500_000, DevelopmentType::BP_957, DevelopmentForm::VERTICAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_957 + VERTICAL → ECONOMIC @ ₱2.5M+1'        => [2_500_001, DevelopmentType::BP_957, DevelopmentForm::VERTICAL, MarketSegment::ECONOMIC, 0.95, 0.35],

        // BP_220 → HORIZONTAL
        'BP_220 + HORIZONTAL → SOCIALIZED @ ₱850k'     => [850_000, DevelopmentType::BP_220, DevelopmentForm::HORIZONTAL, MarketSegment::SOCIALIZED, 1.00, 0.35],
        'BP_220 + HORIZONTAL → ECONOMIC @ ₱850,001'    => [850_001, DevelopmentType::BP_220, DevelopmentForm::HORIZONTAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_220 + HORIZONTAL → ECONOMIC @ ₱2.5M'       => [2_500_000, DevelopmentType::BP_220, DevelopmentForm::HORIZONTAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_220 + HORIZONTAL → OPEN @ ₱2.5M+1'         => [2_500_001, DevelopmentType::BP_220, DevelopmentForm::HORIZONTAL, MarketSegment::OPEN, 0.90, 0.30],

        // BP_220 → VERTICAL
        'BP_220 + VERTICAL → SOCIALIZED @ ₱1.8M'       => [1_800_000, DevelopmentType::BP_220, DevelopmentForm::VERTICAL, MarketSegment::SOCIALIZED, 1.00, 0.35],
        'BP_220 + VERTICAL → ECONOMIC @ ₱1.8M+1'       => [1_800_001, DevelopmentType::BP_220, DevelopmentForm::VERTICAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_220 + VERTICAL → ECONOMIC @ ₱2.5M'         => [2_500_000, DevelopmentType::BP_220, DevelopmentForm::VERTICAL, MarketSegment::ECONOMIC, 0.95, 0.35],
        'BP_220 + VERTICAL → OPEN @ ₱2.5M+1'           => [2_500_001, DevelopmentType::BP_220, DevelopmentForm::VERTICAL, MarketSegment::OPEN, 0.90, 0.30],
        'BP_220 + VERTICAL → OPEN @ ₱5M'               => [5_000_000, DevelopmentType::BP_220, DevelopmentForm::VERTICAL, MarketSegment::OPEN, 0.90, 0.30],
    ];
});

it('determines market segment and financial multipliers correctly', function (
    float $tcp,
    DevelopmentType $dev,
    DevelopmentForm $form,
    MarketSegment $expectedSegment,
    float $expectedLoanableMultiplier,
    float $expectedDisposableMultiplier
) {
    $segment = MarketSegment::fromPrice($tcp, $dev, $form);

    expect($segment)->toBe($expectedSegment)
        ->and($segment->defaultPercentDisposableIncomeRequirement())->toEqualPercent($expectedDisposableMultiplier)
        ->and($segment->defaultPercentLoanableValue())->toEqualPercent($expectedLoanableMultiplier)
    ;
})->with('market_segments');

dataset('interest_rate_cases', function () {
    return [
        // SOCIALIZED
        'Socialized → 749,999' => [MarketSegment::SOCIALIZED, 749_999, 0.03],
        'Socialized → 750,000' => [MarketSegment::SOCIALIZED, 750_000, 0.03],
        'Socialized → 850,000' => [MarketSegment::SOCIALIZED, 850_000, 0.0625],
        'Socialized → 850,001' => [MarketSegment::SOCIALIZED, 850_001, 0.0625],

        // ECONOMIC
        'Economic → 749,999' => [MarketSegment::ECONOMIC, 749_999, 0.03],
        'Economic → 750,000' => [MarketSegment::ECONOMIC, 750_000, 0.03],
        'Economic → 850,000' => [MarketSegment::ECONOMIC, 850_000, 0.0625],
        'Economic → 850,001' => [MarketSegment::ECONOMIC, 850_001, 0.0625],

        // OPEN
        'Open → any value' => [MarketSegment::OPEN, 700_000, 0.07],
    ];
});

it('returns the correct default interest rate for each segment', function (
    MarketSegment $segment,
    float $amount,
    float $expected
) {
    $rate = $segment->defaultInterestRateFor($amount);
    expect($rate)->toEqualPercent($expected);
})->with('interest_rate_cases');
