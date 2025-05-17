<?php

namespace LBHurtado\Mortgage\Calculators;

use Brick\Money\Money;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\Enums\CalculatorType;
use LBHurtado\Mortgage\Enums\ExtractorType;
use LBHurtado\Mortgage\Factories\FeeRulesFactory;
use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\Contracts\FeeRulesInterface;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::MISCELLANEOUS_FEES)]
final class MiscellaneousFeeCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        $tcp = ExtractorFactory::make(ExtractorType::TOTAL_CONTRACT_PRICE, $this->inputs)->toFloat();
        $percent_mf = ExtractorFactory::make(ExtractorType::PERCENT_MISCELLANEOUS_FEES, $this->inputs)->toFloat();

        return MoneyFactory::price($tcp * $percent_mf);
    }

    public function partial(): Price
    {
        $value = $this->calculate();

        $tcp = ExtractorFactory::make(ExtractorType::TOTAL_CONTRACT_PRICE, $this->inputs)->toFloat();
        $percent_dp = ExtractorFactory::make(ExtractorType::PERCENT_DOWN_PAYMENT, $this->inputs)->extract()->value();

        $lending_institution = ExtractorFactory::make(ExtractorType::LENDING_INSTITUTION, $this->inputs)->extract();
        $rules = FeeRulesFactory::make(institution: $lending_institution);

        $shouldApplyPartial = $rules->shouldApplyMiscellaneousFee($tcp);
        $override = $shouldApplyPartial
            ? $rules->getPartialMiscellaneousFeeMultiplier($tcp, Percent::ofFraction($percent_dp))?->value()
            : null;

        $multiplier = $override !== null ? $override : 1.0;

        return $value->multipliedBy($multiplier);
    }

    public function toFloat(): float
    {
        return $this->calculate()->inclusive()->getAmount()->toFloat();
    }
}
