<?php

namespace LBHurtado\Mortgage\Transformers;

use LBHurtado\Mortgage\Data\Match\MatchResultData;

class MatchResultTransformer
{
    public static function transform(MatchResultData $match): array
    {
        return [
            'product_code' => $match->product_code,
            'qualified' => $match->qualified,
            'reason' => $match->reason,

            'monthly_amortization' => $match->monthly_amortization->inclusive()->getAmount()->toFloat(),
            'income_required' => $match->income_required->inclusive()->getAmount()->toFloat(),
            'suggested_equity' => $match->suggested_equity->inclusive()->getAmount()->toFloat(),
            'income_gap' => $match->gap,
        ];
    }

    /**
     * Transform a collection of MatchResultData.
     */
    public static function collection(iterable $matches): array
    {
        return collect($matches)
            ->map(fn (MatchResultData $match) => self::transform($match))
            ->values()
            ->all();
    }
}
