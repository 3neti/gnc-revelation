<?php

namespace App\Classes;

use App\Data\QualificationComputationData;
use App\Modifiers\OtherIncomeModifier;
use App\Contracts\PropertyInterface;
use Brick\Math\RoundingMode;
use Whitecube\Price\Price;
use Brick\Money\Money;

trait BuyerExtension
{
    public function getJointMonthlyDisposableIncome(): Price
    {
        $total = new Price($this->getMonthlyDisposableIncome()->inclusive());
        $this->co_borrowers->each(function (Buyer $co_borrower) use ($total) {
            $total->addModifier(
                'co-borrower: ' . $co_borrower->getBirthdate()->toDateString(),
                $co_borrower->getMonthlyDisposableIncome()->inclusive(),
                roundingMode: RoundingMode::CEILING
            );
        });

        return $total;
    }

    public function getDownPaymentTerm(): ?int
    {
        // Default from config, or allow future override logic here
        return config('gnc-revelation.defaults.buyer.down_payment_term');
    }

    public function getBalancePaymentTerm(): ?int
    {
        // The balance term is based on the buyer’s joint qualification term
        return $this->getJointMaximumTermAllowed();
    }

    public function resolveBufferMargin(PropertyInterface $property): float
    {
        $propertyBuffer = $property->getRequiredBufferMargin(); // ?Percent
        if ($propertyBuffer !== null) {
            return $propertyBuffer->value(); // -> float
        }

        $institutionBuffer = $this->lendingInstitution->getRequiredBufferMargin(); // float|null
        if (is_float($institutionBuffer)) {
            return $institutionBuffer;
        }

        return (float) config('gnc-revelation.default_buffer_margin', 0.1);
    }

    public function addOtherSourcesOfIncome(string $name, Money|float $value, string $tag = 'unclassified'): static
    {
        $money = $value instanceof Money ? $value : Money::of($value, 'PHP');

        // Add to Price as modifier
        $this->monthly_gross_income->addModifier(
            $name,
            OtherIncomeModifier::class,
            $money
        );

        // Track metadata
        $this->other_income_sources[] = [
            'name' => $name,
            'amount' => $money->getAmount()->toFloat(),
            'money' => $money,
            'tag' => $tag,
        ];

        return $this;
    }

    public function getFormattedIncomeBreakdown(): array
    {
        return collect($this->other_income_sources)
            ->groupBy('tag')
            ->map(fn ($items) =>
            $items->map(fn ($source) => [
                'name' => $source['name'],
                'amount' => $source['money']->getAmount()->toFloat(),
            ])->all()
            )
            ->all();
    }

    public function getIncomeBreakdownByTag(): array
    {
        return collect($this->other_income_sources)
            ->groupBy('tag')
            ->map(fn ($items) =>
            collect($items)->sum('amount')
            )
            ->all();
    }

    public function getQualificationComputation(PropertyInterface $property): QualificationComputationData
    {
        return QualificationComputationData::fromBuyerAndProperty($this, $property);
    }
}
