<?php

namespace LBHurtado\Mortgage\Services;

use LBHurtado\Mortgage\Data\Match\{LoanProductData, MatchResultData};
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\Data\QualificationResultData;
use LBHurtado\Mortgage\Classes\{Buyer, Order, Property};
use Illuminate\Support\Collection;

class LoanMatcherService
{
    /**
     * @param  Buyer  $buyer
     * @param  Collection<int, LoanProductData>  $products
     * @return Collection<int, MatchResultData>
     */
    public function match(Buyer $buyer, Collection $products): Collection
    {
        return $products->map(function (LoanProductData $product) use ($buyer): MatchResultData {
            $termYears = min(
                $buyer->getJointMaximumTermAllowed(),
                $product->max_term_years,
            );

            $property = new Property($product->tcp);

            $order = (new Order())
                ->setInterestRate($product->interest_rate)
                ->setBalancePaymentTerm($termYears)
                ->setIncomeRequirementMultiplier($product->disposable_income_multiplier)
                ->setProcessingFee(0);

            $inputs = InputsData::fromBooking($buyer, $property, $order);
            $result = QualificationResultData::fromInputs($inputs);

            return new MatchResultData(
                qualified: $result->qualifies,
                product_code: $product->code,
                monthly_amortization: $result->monthly_amortization,
                income_required: $result->income_required,
                suggested_equity: $result->loan_difference,
                gap: $result->income_gap->inclusive()->getAmount()->toFloat(),
                reason: $result->reason,
            );
        });
    }

    /**
     * Filter and return only qualified products
     *
     * @param  Buyer  $buyer
     * @param  Collection<int, LoanProductData>  $products
     * @return Collection<int, MatchResultData>
     */
    public function matchQualifiedOnly(Buyer $buyer, Collection $products): Collection
    {
        return $this->match($buyer, $products)
            ->filter(fn (MatchResultData $result) => $result->qualified)
            ->values(); // reindex the collection
    }
}
