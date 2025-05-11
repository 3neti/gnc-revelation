<?php

use LBHurtado\Mortgage\Enums\Property\DevelopmentType;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\ValueObjects\Percent;

it('returns correct name for each development type', function () {
    expect(DevelopmentType::BP_220->getName())->toBe('Open Market Housing')
        ->and(DevelopmentType::BP_957->getName())->toBe('Socialized/Economic Market Housing');
});

it('returns valid options array', function () {
    $options = DevelopmentType::options();

    expect($options)->toBeArray()
        ->and($options)->toContain(
            ['value' => 'bp_220', 'label' => 'Open Market Housing'],
            ['value' => 'bp_957', 'label' => 'Socialized/Economic Market Housing'],
        );
});

it('returns correct default percent maximum loanable amount', function () {
    expect(DevelopmentType::BP_220->getDefaultPercentMaximumLoanableAmount())
        ->toEqual(Percent::ofPercent(95))
        ->and(DevelopmentType::BP_957->getDefaultPercentMaximumLoanableAmount())
        ->toEqual(Percent::ofPercent(90));
});

it('returns correct default maximum loanable amount', function () {
    expect(DevelopmentType::BP_220->getDefaultMaximumLoanableAmount())
        ->toEqual(MoneyFactory::priceWithPrecision(2_500_000))
        ->and(DevelopmentType::BP_957->getDefaultMaximumLoanableAmount())
        ->toEqual(MoneyFactory::priceWithPrecision(6_000_000));
});
