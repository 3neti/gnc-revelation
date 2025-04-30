<?php

namespace LBHurtado\Mortgage\Rules;

use LBHurtado\Mortgage\Contracts\FeeRulesInterface;
use LBHurtado\Mortgage\ValueObjects\Percent;

class FeeRules implements FeeRulesInterface
{
    /**
     * Use down payment percentage as the MF multiplier by default.
     */
    public function getPartialMiscellaneousFeeMultiplier(float $tcp, Percent $percentDp): ?Percent
    {
        return null;
    }

    public function shouldApplyMiscellaneousFee(float $tcp): bool
    {
        return true;
    }
}
