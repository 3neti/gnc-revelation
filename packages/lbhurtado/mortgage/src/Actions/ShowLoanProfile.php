<?php

namespace LBHurtado\Mortgage\Actions;

use LBHurtado\Mortgage\Models\LoanProfile;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowLoanProfile
{
    use AsAction;

    public function handle(string $reference_code): LoanProfile
    {
        return LoanProfile::where('reference_code', $reference_code)->firstOrFail();
    }

    public function asController(string $reference_code): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->handle($reference_code));
    }
}
