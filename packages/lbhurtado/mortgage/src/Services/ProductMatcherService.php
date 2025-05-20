<?php

namespace LBHurtado\Mortgage\Services;

use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\Data\MortgageComputationData;
use LBHurtado\Mortgage\Classes\{Buyer, Order};
use LBHurtado\Mortgage\Models\Product;
use Illuminate\Support\Collection;

class ProductMatcherService
{
    /**
     * Match a buyer to properties filtered by price and lending institution.
     *
     * @param Buyer $buyer
     * @param int|null $price_limit
     * @param array|string|null $lending_institutions
     * @return Collection
     */
    public function match(Buyer $buyer, ?int $price_limit = null, array|string|null $lending_institutions = null): Collection
    {
        // Fetch Products and their associated Properties using scopes
        $products = Product::query()
            ->filterByPrice($price_limit) // Filter by price using Product scope
            ->whereHas('properties', fn ($query) => $query->forLendingInstitution($lending_institutions)) // Filter using Property scope
            ->with('properties') // Eager load properties
            ->get()
        ;
//        dd($products->toSql());
//dd($products->count());
        // Perform matching and generate computation data for each product and its properties
        return $products->map(function (Product $product) use ($buyer): MortgageComputationData {
            $matchingProperty = $product->properties->first(); // Assuming we process the first matching property (adjust logic if needed)
            $matchingProperty->setUseTypicalPrice(true);
            $mortgageParticulars = MortgageParticulars::fromBooking($buyer, $matchingProperty->toDomain(), new Order);

            return tap(MortgageComputationData::fromParticulars($mortgageParticulars), function (MortgageComputationData $data) use ($product) {
                $data->setProductCode($product->sku);
            });
        });
    }

    /**
     * Return only qualified properties based on buyer profile.
     *
     * @param Buyer $buyer
     * @param int|null $price_limit
     * @param array|string|null $lending_institutions
     * @return Collection
     */
    public function matchQualifiedOnly(Buyer $buyer, ?int $price_limit = null, array|string|null $lending_institutions = null): Collection
    {
        return $this->match($buyer, $price_limit, $lending_institutions)
            ->filter(fn (MortgageComputationData $result) => $result->qualifies)
            ->values(); // Reindex the collection
    }
}
