<?php

namespace LBHurtado\Mortgage\Factories;

use LBHurtado\Mortgage\Extractors\{BaseExtractor,
    IncomeRequirementMultiplierExtractor,
    InterestRateExtractor,
    LendingInstitutionExtractor,
    PercentDownPaymentExtractor,
    PercentMiscellaneousFeesExtractor,
    ProcessingFeeExtractor,
    TotalContractPriceExtractor};
use LBHurtado\Mortgage\Attributes\ExtractorFor;
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\Enums\ExtractorType;
use RuntimeException;
use ReflectionClass;

final class ExtractorFactory
{
    /**
     * A cached map of ExtractorType string values to their extractor class strings.
     *
     * @var array<string, class-string<BaseExtractor>>
     */
    protected static array $map = [];

    /**
     * Build and return the extractor based on type.
     *
     * @param ExtractorType $type
     * @param MortgageParticulars $inputs
     * @return BaseExtractor
     * @throws \ReflectionException
     */
    public static function make(ExtractorType $type, MortgageParticulars $inputs): BaseExtractor
    {
        static::discoverExtractors();

        if (!array_key_exists($type->value, static::$map)) {
            throw new RuntimeException("No extractor found for type: {$type->value}");
        }

        $class = static::$map[$type->value];

        return $class::fromInputs($inputs);
    }

    /**
     * Discovers extractor classes and caches their mappings.
     *
     * @return void
     * @throws \ReflectionException
     * @todo auto-discovery via filesystem scan
     */
    protected static function discoverExtractors(): void
    {
        if (!empty(static::$map)) {
            return; // Already discovered
        }

        $classes = [
            IncomeRequirementMultiplierExtractor::class,
            LendingInstitutionExtractor::class,
            InterestRateExtractor::class,
            TotalContractPriceExtractor::class,
            PercentDownPaymentExtractor::class,
            PercentMiscellaneousFeesExtractor::class,
            ProcessingFeeExtractor::class,
        ];

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(ExtractorFor::class);

            foreach ($attributes as $attribute) {
                /** @var ExtractorFor $instance */
                $instance = $attribute->newInstance();

                static::$map[$instance->type->value] = $class;
            }
        }
    }
}
