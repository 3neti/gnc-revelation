<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Services\ProductMatcherService;
use LBHurtado\Mortgage\Classes\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductMatchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        // Step 1: Validate the input data
        $data = $request->validate([
            'age' => 'required|integer|min:18|max:65',
            'monthly_income' => 'required|numeric|min:10000',
            'additional_income' => 'nullable|numeric|min:0',
            'co_borrower_age' => 'nullable|integer|min:18|max:60',
            'co_borrower_income' => 'required_with:co_borrower_age|numeric|min:1000',
            'lending_institution' => 'nullable|string|in:hdmf,rcbc,cbc',//TODO: update this in the future
            'price_limit' => 'nullable|numeric|min:800000'
        ]);

        // Step 2: Create a Buyer instance with the data
        $buyer = app(Buyer::class)
            ->setAge($data['age'])
            ->setMonthlyGrossIncome($data['monthly_income']);

        if (!empty($data['co_borrower_age'])) {
            $co_borrower = app(Buyer::class)
                ->setAge($data['co_borrower_age'])
                ->setMonthlyGrossIncome($data['co_borrower_income']);
            $buyer->addCoBorrower($co_borrower);
        }

        // Add additional income if provided
        if (!empty($data['additional_income'])) {
            $buyer->addOtherSourcesOfIncome('extra', $data['additional_income']);
        }

        $price_limit = Arr::get($data, 'price_limit');
        $lending_institution = Arr::get($data, 'lending_institution');

        // Step 3: Initialize the ProductMatcherService
        $service = new ProductMatcherService();

        // Step 4: Call the service to match products
        $results = $service->matchQualifiedOnly(
            buyer: $buyer,
            price_limit: $price_limit, // Optional; you can handle price limit from request if needed
            lending_institutions: $lending_institution // Optional; can handle lending institution from request
        );

        // Step 5: Return the results as a response
        return response()->json([
            'success' => true,
            'data' => $results->toArray(),
        ]);
    }
}
