<?php

namespace LBHurtado\Mortgage\Actions;

use LBHurtado\Mortgage\Factories\MortgageParticularsFactory;
use LBHurtado\Mortgage\Data\Inputs\MortgageInputsData;
use LBHurtado\Mortgage\Data\MortgageComputationData;
use LBHurtado\Mortgage\Models\LoanProfile;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateLoanProfile
{
    use AsAction;

    public function handle(MortgageInputsData $inputsData): LoanProfile
    {
        $particulars = MortgageParticularsFactory::fromData($inputsData);
        $computation = MortgageComputationData::fromParticulars($particulars);

        return LoanProfile::create([
            'lending_institution' => $computation->lending_institution->key(),
            'total_contract_price' => $computation->total_contract_price->inclusive()->getAmount()->toFloat(),

            'inputs' => $inputsData->toArray(),
            'computation' => $computation->toArray(),

            'qualified' => $computation->qualifies(),
            'required_equity' => $computation->required_equity->getAmount()->toFloat(),
            'income_gap' => $computation->income_gap->getAmount()->toFloat(),
            'suggested_down_payment_percent' => $computation->percent_down_payment_remedy->value(),
            'reason' => $computation->reason(),

            'reserved_at' => null,
        ]);
    }

    public function asController(MortgageInputsData $inputsData): \Illuminate\Http\JsonResponse
    {
        $loanProfile = $this->handle($inputsData);

        return response()->json($loanProfile);
    }
}
