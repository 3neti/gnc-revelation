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
            'additional_income' => 'nullable|array',
            'additional_income.name' => 'required_with:additional_income|string',
            'additional_income.amount' => 'required_with:additional_income|numeric|min:0',
            'co_borrower' => 'nullable|array',
            'co_borrower.age' => 'required_with:co_borrower|integer|min:18|max:65',
            'co_borrower.monthly_income' => 'required_with:co_borrower|numeric|min:1000',
            'lending_institution' => 'nullable|string|in:hdmf,rcbc,cbc',//TODO: update this in the future
            'price_limit' => 'nullable|numeric|min:800000'
        ]);

        // Step 2: Create a Buyer instance with the data
        $buyer = app(Buyer::class)
            ->setAge($data['age'])
            ->setMonthlyGrossIncome($data['monthly_income']);

        if (!empty($data['co_borrower'])) {
            $co_borrower = app(Buyer::class)
                ->setAge($data['co_borrower']['age'])
                ->setMonthlyGrossIncome($data['co_borrower']['monthly_income']);
            $buyer->addCoBorrower($co_borrower);
        }

        // Add additional income if provided
        if (!empty($data['additional_income'])) {
            $buyer->addOtherSourcesOfIncome($data['additional_income']['name'], $data['additional_income']['amount']);
        }

        // Add co-borrower if provided
        if (!empty($data['co_borrower'])) {
            $coBorrower = app(Buyer::class);
            $coBorrower->setAge($data['co_borrower']['age']);
            $coBorrower->setMonthlyGrossIncome($data['co_borrower']['monthly_income']);
            $buyer->addCoBorrower($coBorrower);
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
