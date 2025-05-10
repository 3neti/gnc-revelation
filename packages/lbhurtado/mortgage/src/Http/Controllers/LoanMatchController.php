<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Transformers\MatchResultTransformer;
use LBHurtado\Mortgage\Classes\{Buyer, LendingInstitution, Property};
use LBHurtado\Mortgage\Services\LoanMatcherService;
use LBHurtado\Mortgage\Data\Match\MatchResultData;
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
            'products.*.tcp' => 'required|numeric|min:10000',
            'products.*.interest_rate' => 'required|numeric|min:0',
        ]);

        $buyer = app(Buyer::class)
            ->setAge($data['age'])
            ->setMonthlyGrossIncome($data['monthly_income'])
            ->setLendingInstitution(new LendingInstitution('hdmf'));

        /** @var \Illuminate\Support\Collection<int, PropertyInterface> $products */
        $products = collect($data['products'])->map(function ($product) {
            return (new Property($product['tcp']))
                ->setInterestRate($product['interest_rate'])
                ->setLendingInstitution(new LendingInstitution('hdmf'))
                ->setCode($product['code']);
        });

        $results = (new LoanMatcherService())
            ->match($buyer, $products);

//        dd($results->toArray()); // <— check if this contains any result with ['qualified' => true]

        $results = (new LoanMatcherService())
            ->match($buyer, $products)
            ->filter(fn (MatchResultData $result) => $result->qualified)
            ->values();

        return MatchResultTransformer::collection($results);
    }
}
