<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Data\Match\{LoanProductData, MatchResultData};
use LBHurtado\Mortgage\Transformers\MatchResultTransformer;
use LBHurtado\Mortgage\Classes\{Buyer, LendingInstitution};
use LBHurtado\Mortgage\Services\LoanMatcherService;
use Illuminate\Http\Request;

class LoanMatchController extends Controller
{
    public function __invoke(Request $request)
    {

        $data = $request->validate([
            'age' => 'required|integer|min:18|max:65',
            'monthly_income' => 'required|numeric|min:1000',
            'products' => 'required|array',
            'products.*.code' => 'required|string',
            'products.*.name' => 'required|string',
            'products.*.tcp' => 'required|numeric|min:10000',
            'products.*.interest_rate' => 'required|numeric|min:0',
            'products.*.max_term_years' => 'required|integer|min:1',
            'products.*.max_loanable_percent' => 'required|numeric|min:0|max:1',
            'products.*.disposable_income_multiplier' => 'required|numeric|min:0|max:1',
        ]);

        $buyer = app(Buyer::class)
            ->setAge($data['age'])
            ->setMonthlyGrossIncome($data['monthly_income'])
            ->setLendingInstitution(new LendingInstitution('hdmf'));


        $products = collect($data['products'])->map(fn ($product) => new LoanProductData(
            code: $product['code'],
            name: $product['name'],
            tcp: $product['tcp'],
            interest_rate: $product['interest_rate'],
            max_term_years: $product['max_term_years'],
            max_loanable_percent: $product['max_loanable_percent'],
            disposable_income_multiplier: $product['disposable_income_multiplier'],
        ));

        $results = (new LoanMatcherService())
            ->match($buyer, $products)
            ->filter(fn (MatchResultData $result) => $result->qualified)
            ->values();

        return MatchResultTransformer::collection($results);
    }
}
