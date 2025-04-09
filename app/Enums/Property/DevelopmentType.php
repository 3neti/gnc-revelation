<?php

namespace App\Enums\Property;

use App\Support\MoneyFactory;
use App\ValueObjects\Percent;
use Whitecube\Price\Price;

enum DevelopmentType: string
{
    case BP_220 = 'bp_220';
    case BP_957 = 'bp_957';

    public function getName(): string
    {
        return match ($this) {
            self::BP_957 => 'BP 957',
            self::BP_220 => 'BP 220',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $type) => ['value' => $type->value, 'label' => $type->getName()],
            self::cases()
        );
    }

    public function getDefaultPercentMaximumLoanableAmount(): ?Percent
    {
        return match ($this) {
            self::BP_220 => Percent::ofPercent(95),
            self::BP_957 => Percent::ofPercent(90),
        };
    }

    public function getDefaultMaximumLoanableAmount(): ?Price
    {
        return match ($this) {
            self::BP_220 => MoneyFactory::priceWithPrecision(2_500_000),
            self::BP_957 => MoneyFactory::priceWithPrecision(6_000_000),
        };
    }
}
