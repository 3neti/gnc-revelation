<?php

namespace App\Enums\Order;

use Brick\Money\Money;

enum MonthlyFee: string
{
    case MRI = 'MRI';
    case FIRE_INSURANCE = 'Fire Insurance';
    case OTHER = 'Other'; // Future-proofing

    public function label(): string
    {
        return $this->value;
    }

    public function configKey(): string
    {
        return match ($this) {
            self::MRI             => 'mri',
            self::FIRE_INSURANCE  => 'fire_insurance',
            self::OTHER           => 'other',
        };
    }

    public function defaultAmount(): Money
    {
        $amount = config("gnc-revelation.order.default.monthly_fees.{$this->configKey()}", 0);
        return Money::of($amount, 'PHP');
    }
}
