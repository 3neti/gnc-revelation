<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use Illuminate\Http\JsonResponse;

class LendingInstitutionController
{
    public function index(): JsonResponse
    {
        $institutions = collect(LendingInstitution::keys())
            ->map(fn($key) => [
                'key' => $key,
                'name' => (new LendingInstitution($key))->name(),
                'alias' => (new LendingInstitution($key))->alias(),
                'type' => (new LendingInstitution($key))->type(),
            ])
            ->values();

        return response()->json($institutions);
    }

    public function show(string $key): JsonResponse
    {
        try {
            $institution = new LendingInstitution($key);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException("Lending institution '{$key}' not found.");
        }

        return response()->json([
            'key' => $institution->key(),
            'name' => $institution->name(),
            'alias' => $institution->alias(),
            'type' => $institution->type(),
            'borrowing_age' => [
                'minimum' => $institution->minimumAge(),
                'maximum' => $institution->maximumAge(),
                'offset' => $institution->offset(),
            ],
            'maximum_term' => $institution->maximumTerm(),
            'maximum_paying_age' => $institution->maximumPayingAge(),
            'buffer_margin' => $institution->getRequiredBufferMargin(),
            'income_requirement_multiplier' => $institution->getIncomeRequirementMultiplier()?->value(),
            'interest_rate' => $institution->getInterestRate()?->value(),
            'percent_down_payment' => $institution->getPercentDownPayment()->value(),
        ]);
    }
}
