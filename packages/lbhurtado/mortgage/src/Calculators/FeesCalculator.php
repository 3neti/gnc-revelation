<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Enums\{CalculatorType, ExtractorType, MonthlyFee};
use Illuminate\Support\Collection;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\ValueObjects\FeeCollection;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Contracts\OrderInterface;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::FEES)]
final class FeesCalculator extends BaseCalculator
{
    public function calculate(): FeeCollection
    {
        $monthlyFees = new FeeCollection();
        $tcp = $this->inputs->property()->getTotalContractPrice()->inclusive()->getAmount()->toFloat();
        $lending_institution = ExtractorFactory::make(ExtractorType::LENDING_INSTITUTION, $this->inputs)->extract();
        $this->inputs->order()->getMonthlyFeeEnums()->each(function (MonthlyFee $monthlyFee) use ($monthlyFees, $tcp, $lending_institution) {
            $price = $monthlyFee->computeFromTCP($tcp, $lending_institution);
            $monthlyFees->addAddOn($monthlyFee->label(), $price->inclusive());
        });

        return $monthlyFees;
    }

    public function mri(): ?Price
    {
        return $this->getFee(MonthlyFee::MRI);
    }

    public function fireInsurance(): ?Price
    {
        return $this->getFee(MonthlyFee::FIRE_INSURANCE);
    }

    public function other(): ?Price
    {
        return $this->getFee(MonthlyFee::OTHER);
    }

    protected function getFee(MonthlyFee $monthlyFee): ?Price
    {
        $value = $this->calculate()->allAddOns()->get($monthlyFee->label());

        return $value
            ? MoneyFactory::priceWithPrecision($value->getAmount()->toFloat())
            : null;
    }

    public function total(): Price
    {
        return MoneyFactory::priceWithPrecision($this->calculate()->totalAddOns());
    }
}
