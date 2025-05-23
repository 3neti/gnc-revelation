<?php

namespace LBHurtado\Mortgage\Factories;

use InvalidArgumentException;
use Brick\Math\RoundingMode;
use Whitecube\Price\Price;
use Brick\Money\Money;

class MoneyFactory
{
    public static function of(float|int|string $amount, ?string $currency = null): Money
    {
        $currency ??= self::getCurrency();
        $roundingMode = self::getRoundingMode();

        return Money::of($amount, $currency, roundingMode: $roundingMode);
    }

    public static function ofWithPrecision(float|int|string $amount, int $precision = 2, ?string $currency = null): Money
    {
        $currency ??= self::getCurrency();
        $rounded = round((float) $amount, $precision);
        return self::of($rounded, $currency);
    }

    public static function price(float|int|string|Money $amount, ?string $currency = null): Price
    {
        if ($amount instanceof Money) {
            return (new Price($amount))->setVat(0); // Provide a default VAT value
        }

        return (new Price(self::of($amount, $currency)))->setVat(0); // Provide a default VAT value
    }

    public static function priceOfMinor(int|string $minorUnits, ?string $currency = null): Price
    {
        $currency ??= self::getCurrency();

        // Convert minor units (int) to a Money object
        $money = Money::ofMinor($minorUnits, $currency);

        return (new Price($money))->setVat(0);
    }

    public static function priceWithPrecision(float|int|string|Money $amount, int $precision = 2, ?string $currency = null): Price
    {
        if ($amount instanceof Money) {
            return (new Price($amount))->setVat(0);
        }

        $currency ??= self::getCurrency();
        $rounded = round((float) $amount, $precision);

        return (new Price(self::of($rounded, $currency)))->setVat(0);
    }

    public static function positivePrice(float|int|string $amount, ?string $currency = null): Price
    {
        return new Price(self::of(max(0, $amount), $currency));
    }

    public static function zero(?string $currency = null): Money
    {
        return self::of(0, $currency);
    }

    public static function priceZero(?string $currency = null): Price
    {
        return new Price(self::zero($currency));
    }

    protected static function getCurrency(): string
    {
        $currency = config('gnc-revelation.default_currency', 'PHP');

        if (! is_string($currency) || strlen($currency) !== 3) {
            throw new InvalidArgumentException("Invalid currency [$currency] configured.");
        }

        return strtoupper($currency);
    }

    protected static function getRoundingMode(): int
    {
        return config('gnc-revelation.rounding_mode', RoundingMode::CEILING);
    }
}
