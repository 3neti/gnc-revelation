<?php

namespace App\Rules;

use App\Contracts\FeeRulesInterface;
use App\ValueObjects\Percent;

class ResidentialFeeRules implements FeeRulesInterface
{
    /**
     * For residential loans, we assume partial miscellaneous fee equals the DP percent.
     * Can be adjusted based on institution-specific rules.
     */
    public function getPartialMiscellaneousFeeMultiplier(float $tcp, Percent $percentDp): ?Percent
    {
        return $percentDp; // Default: use DP percentage
    }

    public function shouldApplyMiscellaneousFee(float $tcp): bool
    {
        // Always apply miscellaneous fees for residential by default
        return false;
    }
}
