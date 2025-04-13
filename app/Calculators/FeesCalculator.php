<?php

namespace App\Calculators;

use App\Attributes\CalculatorFor;
use App\Contracts\OrderInterface;
use App\Data\Inputs\InputsData;
use App\Enums\CalculatorType;
use App\Enums\Order\MonthlyFee;
use App\Support\MoneyFactory;
use App\ValueObjects\FeeCollection;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::FEES)]
final class FeesCalculator extends BaseCalculator
{
    public function calculate(): FeeCollection
    {
        $order = $this->inputs->order();
        $collection = $order instanceof OrderInterface
            ? $order->getMonthlyFees()
            : new FeeCollection();

        return $collection;
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

    protected function getFee(MonthlyFee $type): ?Price
    {
        $value = $this->calculate()->allAddOns()->get($type->label());

        return $value
            ? MoneyFactory::priceWithPrecision($value->getAmount()->toFloat())
            : null;
    }

    public function total(): Price
    {
        return MoneyFactory::priceWithPrecision($this->calculate()->totalAddOns());
    }
}
