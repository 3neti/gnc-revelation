<?php

namespace LBHurtado\Mortgage\Enums\Property;

use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;
use Brick\Money\Money;

enum MarketSegment: string
{
    case OPEN = 'open';
    case ECONOMIC = 'economic';
    case SOCIALIZED = 'socialized';

    public static function fromPrice(
        Price|Money|float|int|string $total_contract_price,
        DevelopmentType $development = DevelopmentType::BP_957,
        DevelopmentForm $form = DevelopmentForm::HORIZONTAL,
    ): self {
        $amount = match (true) {
            $total_contract_price instanceof Price => $total_contract_price->base()->getAmount()->toFloat(),
            $total_contract_price instanceof Money => $total_contract_price->getAmount()->toFloat(),
            default => (float) $total_contract_price,
        };

        $segmentCeilings = config("gnc-revelation.property.market.ceiling.{$development->value}.{$form->value}");
        if (!is_array($segmentCeilings)) {
            throw new \RuntimeException("Market segment ceilings are not properly configured for development type [{$development->value}] and form [{$form->value}].");
        }

        if (
            !isset($segmentCeilings['socialized'], $segmentCeilings['economic'], $segmentCeilings['open']) ||
            !is_numeric($segmentCeilings['socialized']) ||
            !is_numeric($segmentCeilings['economic']) ||
            !is_numeric($segmentCeilings['open'])
        ) {
            throw new \RuntimeException("Ceilings must include numeric values for socialized, economic, and open.");
        }

        return match (true) {
            $amount <= $segmentCeilings['socialized'] => self::SOCIALIZED,
            $amount <= $segmentCeilings['economic'] => self::ECONOMIC,
            default => self::OPEN,
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

    public function defaultIncomeRequirementMultiplier(): Percent
    {
        return match ($this) {
            self::OPEN => Percent::ofFraction(config('gnc-revelation.property.market.percent_disposable_income.open', 0.30)),
            self::ECONOMIC => Percent::ofFraction(config('gnc-revelation.property.market.percent_disposable_income.economic', 0.35)),
            self::SOCIALIZED => Percent::ofFraction(config('gnc-revelation.property.market.percent_disposable_income.socialized', 0.35)),
        };
    }

    public function defaultPercentLoanableValue(): Percent
    {
        return match ($this) {
            self::OPEN => Percent::ofFraction(config('gnc-revelation.property.market.percent_loanable_value.open', 0.90)),
            self::ECONOMIC => Percent::ofFraction(config('gnc-revelation.property.market.percent_loanable_value.economic', 0.95)),
            self::SOCIALIZED => Percent::ofFraction(config('gnc-revelation.property.market.percent_loanable_value.socialized', 1.00)),
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

    public static function hasCeilingConfig(DevelopmentType $dev, DevelopmentForm $form): bool
    {
        return is_array(config("gnc-revelation.property.market.ceiling.{$dev->value}.{$form->value}"));
    }

    public function defaultInterestRateFor(float|Money|Price $value): Percent
    {
        $amount = match (true) {
            $value instanceof Price => $value->inclusive()->getAmount()->toFloat(),
            $value instanceof Money => $value->getAmount()->toFloat(),
            default => (float) $value,
        };

        return match ($this) {
            self::OPEN => Percent::ofFraction(0.07),

            self::SOCIALIZED, self::ECONOMIC => match (true) {
                $amount <= 750_000 => Percent::ofFraction(0.03),
                $amount <= 850_000 => Percent::ofFraction(0.0625),
                default             => Percent::ofFraction(0.0625),
            },
        };
    }
}
