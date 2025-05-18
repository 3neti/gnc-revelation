<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Factories\MortgageParticularsFactory;
use LBHurtado\Mortgage\Data\Inputs\MortgageInputsData;
use LBHurtado\Mortgage\Data\MortgageComputationData;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;

class MortgageComputationController extends Controller
{
    public function __invoke(MortgageInputsData $mortgage_input_data): JsonResponse
    {
        $mortgage_particulars = MortgageParticularsFactory::fromData($mortgage_input_data);
        $mortgage_computation = MortgageComputationData::fromParticulars($mortgage_particulars);
        $payload = $mortgage_computation->toArray();
        $qualification = (object) $payload;

        return response()->json([
            'payload' => $payload,
            'qualification' => [
                'income_gap' => $qualification->income_gap,
                'required_equity' => $qualification->required_equity,
                'suggested_down_payment_percent' => $qualification->percent_down_payment_remedy,
                'qualifies' => $mortgage_computation->qualifies(),
                'reason' => $mortgage_computation->reason(),
                'mortgage' => [
                    'monthly_amortization' => $qualification->monthly_amortization,
                    'balance_payment_term' => $qualification->balance_payment_term,
                ],
            ],
        ]);
    }
}
