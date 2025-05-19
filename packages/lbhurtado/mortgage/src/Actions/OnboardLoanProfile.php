<?php

namespace LBHurtado\Mortgage\Actions;

use LBHurtado\Mortgage\Models\LoanProfile;
use Lorisleiva\Actions\Concerns\AsAction;

class OnboardLoanProfile
{
    use AsAction;

    public function handle(string $reference_code): string
    {
        LoanProfile::where('reference_code', $reference_code)->firstOrFail();

        // Build URL from config
        $baseUrl = config('gnc-revelation.onboarding.url');
        $field_name = config('gnc-revelation.onboarding.field_name');

        return "{$baseUrl}?{$field_name}={$reference_code}";
    }

    public function asController(string $reference_code): \Illuminate\Http\JsonResponse
    {
        $url = $this->handle($reference_code);

        return response()->json(['url' => $url]);
    }
}
