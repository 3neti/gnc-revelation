<?php

namespace LBHurtado\Mortgage\Enums;

use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use Whitecube\Price\Price;
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


    public function computeFromTCP(float $tcp, LendingInstitution $institution): Price
    {
        return match ($this) {
            self::MRI => MoneyFactory::priceWithPrecision(
                ($tcp / 1000) * $institution->get('mri_rate', 0.225)
            ),

            self::FIRE_INSURANCE => MoneyFactory::priceWithPrecision(
            // Annual fire insurance rate divided by 12 to get monthly value
                ($tcp * $institution->get('fire_insurance_rate', 0.00212584)) / 12
            ),

            self::OTHER => MoneyFactory::zero(),
        };
    }
}
