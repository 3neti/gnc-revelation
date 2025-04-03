<?php

namespace App\Services;

use App\Data\{LoanProductData, MatchResultData};
use Illuminate\Support\Collection;
use App\DataObjects\MortgageTerm;
use Whitecube\Price\Price;
use App\Classes\Buyer;

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
            // Use buyer's max eligible term based on joint borrowers and institution rules
            $termYears = min(
                $buyer->getJointMaximumTermAllowed(),
                $product->max_term_years
            );

            $calculator = new PurchasePlanCalculator(
                principal: $product->tcp,
                interestRate: $product->interest_rate,
                term: new MortgageTerm($termYears),
                disposableMultiplier: $product->disposable_income_multiplier
            );

            $result = $calculator->getQualificationResult(
                $buyer->getJointMonthlyDisposableIncome()->inclusive()
            );

            return new MatchResultData(
                qualified: $result->qualifies,
                product_code: $product->code,
                monthly_amortization: $result->monthly_amortization,
                income_required: new Price($result->income_required),
                suggested_equity: $result->suggested_equity,
                gap: $result->gap,
                reason: $result->reason
            );
        });
    }
}
