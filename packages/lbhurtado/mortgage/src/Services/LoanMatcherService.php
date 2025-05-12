<?php

namespace LBHurtado\Mortgage\Services;

use LBHurtado\Mortgage\Data\QualificationResultData;
use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Data\Match\MatchResultData;
use LBHurtado\Mortgage\Classes\{Buyer, Order};
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use Illuminate\Support\Collection;

class LoanMatcherService
{
    /**
     * Match a buyer to all provided properties and return qualification results.
     *
     * @param Buyer $buyer
     * @param Collection<int, PropertyInterface> $properties
     * @return Collection<int, MatchResultData>
     */
    public function match(Buyer $buyer, Collection $properties): Collection
    {
        return $properties->map(function (PropertyInterface $property) use ($buyer): MatchResultData {
            $order = new Order;
            $inputs = InputsData::fromBooking($buyer, $property, $order);
            $result = QualificationResultData::fromInputs($inputs);

            return new MatchResultData(
                qualified: $result->qualifies,
                product_code: method_exists($property, 'getCode') ? $property->getCode() : 'N/A',
                monthly_amortization: $result->monthly_amortization,
                income_required: $result->income_required,
                suggested_equity: $result->loan_difference,
                gap: $result->income_gap->inclusive()->getAmount()->toFloat(),
                reason: $result->reason
            );
        });
    }

    /**
     * Return only qualified properties based on buyer profile.
     *
     * @param Buyer $buyer
     * @param Collection<int, PropertyInterface> $properties
     * @return Collection<int, MatchResultData>
     */
    public function matchQualifiedOnly(Buyer $buyer, Collection $properties): Collection
    {
        return $this->match($buyer, $properties)
            ->filter(fn (MatchResultData $result) => $result->qualified)
            ->values(); // reindex
    }
}
