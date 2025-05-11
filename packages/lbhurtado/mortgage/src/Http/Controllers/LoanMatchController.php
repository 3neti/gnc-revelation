<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Transformers\MatchResultTransformer;
use LBHurtado\Mortgage\Models\Property as PropertyModel;
use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Services\LoanMatcherService;
use LBHurtado\Mortgage\Data\Match\MatchResultData;
use LBHurtado\Mortgage\Classes\Buyer;
use Illuminate\Http\Request;

class LoanMatchController extends Controller
{
    public function __invoke(Request $request): array
    {
        $data = $request->validate([
            'age' => 'required|integer|min:18|max:65',
            'monthly_income' => 'required|numeric|min:1000',
            'additional_income' => 'nullable|numeric|min:0',
            'co_borrower.age' => 'nullable|integer|min:18|max:65',
            'co_borrower.monthly_income' => 'nullable|numeric|min:1000',
            'development_form' => 'nullable|string|in:horizontal,vertical',
            'project_code' => 'nullable|string',
            'house_type' => 'nullable|string',
        ]);

        $buyer = app(Buyer::class)
            ->setAge($data['age'])
            ->setMonthlyGrossIncome($data['monthly_income'])
        ;

        if (!empty($data['additional_income'])) {
            $buyer->addOtherSourcesOfIncome('Other', $data['additional_income']);
        }

        if (!empty($data['co_borrower'])) {
            $coBorrower = app(Buyer::class)
                ->setAge($data['co_borrower']['age'])
                ->setMonthlyGrossIncome($data['co_borrower']['monthly_income']);

            $buyer->addCoBorrower($coBorrower);
        }

        $query = PropertyModel::query()->where('status', 'available');

        if (!empty($data['development_form'])) {
            $query->where('development_form', $data['development_form']);
        }

        if (!empty($data['project_code'])) {
            $query->where('project_code', $data['project_code']);
        }

        if (!empty($data['house_type'])) {
            $query->where('type', $data['house_type']);
        }

        /** @var \Illuminate\Support\Collection<int, PropertyInterface> $properties */
        $properties = $query->get()->map(fn (PropertyModel $property) => $property->toDomain());

        $results = (new LoanMatcherService())
            ->match($buyer, $properties)
            ->filter(fn (MatchResultData $result) => $result->qualified)
            ->values();

        return MatchResultTransformer::collection($results);
    }
}
