<?php

namespace LBHurtado\Mortgage\Services;

use LBHurtado\Mortgage\Data\MortgageComputationData;
use LBHurtado\Mortgage\Data\QualificationResultData;
use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Data\Match\MatchResultData;
use LBHurtado\Mortgage\Classes\{Buyer, Order};
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
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
        return $properties->map(function (PropertyInterface $property) use ($buyer): MortgageComputationData {
            $mortgage_particulars = MortgageParticulars::fromBooking($buyer, $property, new Order);

            $result = MortgageComputationData::fromParticulars($mortgage_particulars);


            return $result;

//            return new MatchResultData(
//                qualified: $result->qualifies(),
//                product_code: method_exists($property, 'getCode') ? $property->getCode() : 'N/A',
//                monthly_amortization: $result->monthly_amortization,
//                income_required: $result->monthly_disposable_income,
//                required_equity: $result->required_equity,
//                income_gap: $result->income_gap,
//                reason: $result->reason()
//            );
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
