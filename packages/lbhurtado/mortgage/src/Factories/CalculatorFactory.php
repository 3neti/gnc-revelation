<?php

namespace LBHurtado\Mortgage\Factories;

use LBHurtado\Mortgage\Calculators\{BaseCalculator, CashOutCalculator, EquityRequirementCalculator, FeesCalculator, LoanAmountCalculator};
use LBHurtado\Mortgage\Calculators\{BalancePaymentTermCalculator,
    IncomeGapCalculator,
    MiscellaneousFeeCalculator,
    PresentValueCalculator,
    LoanQualificationCalculator,
    MonthlyAmortizationCalculator,
    MonthlyDisposableIncomeCalculator,
    RequiredIncomeCalculator,
    RequiredPercentDownPaymentCalculator};
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\Enums\CalculatorType;
use RuntimeException;
use ReflectionClass;

final class CalculatorFactory
{
    /**
     * A cached map of CalculatorType string values to their calculator class strings.
     *
     * @var array<string, class-string<BaseCalculator>>
     */
    protected static array $map = [];

    /**
     * Build and return the calculator based on type.
     *
     * @param CalculatorType $type
     * @param InputsData $inputs
     * @return BaseCalculator
     * @throws \ReflectionException
     */
    public static function make(CalculatorType $type, InputsData $inputs): BaseCalculator
    {
        static::discoverCalculators();

        if (!array_key_exists($type->value, static::$map)) {
            throw new RuntimeException("No calculator found for type: {$type->value}");
        }

        $class = static::$map[$type->value];

        return $class::fromInputs($inputs);
    }

    /**
     * Discovers calculator classes and caches their mappings.
     *
     * @return void
     * @throws \ReflectionException
     * @todo auto-discovery via filesystem scan
     */
    protected static function discoverCalculators(): void
    {
        if (!empty(static::$map)) {
            return; // Already discovered
        }

        $classes = [
            MonthlyAmortizationCalculator::class,
            MonthlyDisposableIncomeCalculator::class,
            PresentValueCalculator::class,
            EquityRequirementCalculator::class,
            CashOutCalculator::class,
            LoanAmountCalculator::class,
            FeesCalculator::class,
            RequiredIncomeCalculator::class,
            IncomeGapCalculator::class,
            LoanQualificationCalculator::class,
            RequiredPercentDownPaymentCalculator::class,
            BalancePaymentTermCalculator::class,
            MiscellaneousFeeCalculator::class
        ];

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(CalculatorFor::class);

            foreach ($attributes as $attribute) {
                /** @var CalculatorFor $instance */
                $instance = $attribute->newInstance();

                static::$map[$instance->type->value] = $class;
            }
        }
    }
}
