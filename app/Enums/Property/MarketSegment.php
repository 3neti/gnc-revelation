<?php

namespace App\Enums\Property;

use Whitecube\Price\Price;

enum MarketSegment: string
{
    case OPEN = 'open';
    case ECONOMIC = 'economic';
    case SOCIALIZED = 'socialized';

    public static function fromPrice(Price $tcp, DevelopmentType $development = DevelopmentType::BP_957): self
    {
        $amount = $tcp->base()->getAmount()->toFloat();

        return match ($development) {
            DevelopmentType::BP_957 => match (true) {
                $amount <= config('gnc-revelation.property.market.ceiling.horizontal.socialized') => self::SOCIALIZED,
                $amount <= config('gnc-revelation.property.market.ceiling.horizontal.economic') => self::ECONOMIC,
                default => self::OPEN,
            },
            DevelopmentType::BP_220 => match (true) {
                $amount <= config('gnc-revelation.property.market.ceiling.vertical.socialized') => self::SOCIALIZED,
                $amount <= config('gnc-revelation.property.market.ceiling.vertical.economic') => self::ECONOMIC,
                default => self::OPEN,
            },
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::OPEN => config('gnc-revelation.property.market.segment.open', 'Open Market'),
            self::ECONOMIC => config('gnc-revelation.property.market.segment.economic', 'Economic'),
            self::SOCIALIZED => config('gnc-revelation.property.market.segment.socialized', 'Socialized'),
        };
    }

    public function defaultDisposableIncomeRequirementMultiplier(): float
    {
        return match ($this) {
            self::OPEN => config('gnc-revelation.property.market.disposable_income_multiplier.open', 0.30),
            self::ECONOMIC => config('gnc-revelation.property.market.disposable_income_multiplier.economic', 0.35),
            self::SOCIALIZED => config('gnc-revelation.property.market.disposable_income_multiplier.socialized', 0.35),
        };
    }

    public function defaultLoanableValueMultiplier(): float
    {
        return match ($this) {
            self::OPEN => config('gnc-revelation.property.market.loanable_value_multiplier.open', 0.90),
            self::ECONOMIC => config('gnc-revelation.property.market.loanable_value_multiplier.economic', 0.95),
            self::SOCIALIZED => config('gnc-revelation.property.market.loanable_value_multiplier.socialized', 1.00),
        };
    }

    public static function default(): self
    {
        return self::SOCIALIZED;
    }

    public static function options(): array
    {
        return array_map(
            fn(self $segment) => ['value' => $segment->value, 'label' => $segment->getName()],
            self::cases()
        );
    }
}
