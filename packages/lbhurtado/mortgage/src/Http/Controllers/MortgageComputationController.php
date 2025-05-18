<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Data\MortgageComputationData;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class MortgageComputationController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'lending_institution' => ['required', 'string'],
            'total_contract_price' => ['required', 'numeric'],
            'percent_down_payment' => ['nullable', 'numeric'],
            'percent_miscellaneous_fee' => ['nullable', 'numeric'],
            'processing_fee' => ['nullable', 'numeric'],
            'add_mri' => ['required', 'boolean'],
            'add_fi' => ['required', 'boolean'],
            'buyer' => ['required', 'array'],
            'buyer.age' => ['required', 'integer'],
            'buyer.monthly_income' => ['required', 'numeric'],
            'buyer.additional_income' => ['required', 'numeric'],
            'co_borrower' => ['nullable', 'array'],
            'co_borrower.age' => ['required_with:co_borrower', 'integer'],
            'co_borrower.monthly_income' => ['required_with:co_borrower', 'numeric'],
        ]);

        $buyer = app('mortgage.buyer.factory')->make($validated['buyer'], $validated['co_borrower'] ?? null);
        $property = app('mortgage.property.factory')->make($validated['total_contract_price'], $validated['lending_institution']);
        $order = app('mortgage.order.factory')->make($validated);

        $inputs = InputsData::fromBooking($buyer, $property, $order);

        $mortgage_computation = MortgageComputationData::fromInputs($inputs);
        $payload = $mortgage_computation->toArray();
        $qualification = (object) $payload;

        return response()->json([
            'payload' => $payload,
            'qualification' => [
                'income_gap' => $qualification->income_gap,
                'loan_difference' => $qualification->required_equity,
                'suggested_down_payment_percent' => $qualification->percent_down_payment_remedy,
                'qualifies' => $mortgage_computation->qualifies(),
                'reason' => $mortgage_computation->reason(),
                'mortgage' => [
                    'monthly_amortization' => $qualification->monthly_amortization,
                    'term_years' => $qualification->balance_payment_term,
                ],
            ],
        ]);
    }
}
