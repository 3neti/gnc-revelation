<?php

namespace LBHurtado\Mortgage\Rules;

use LBHurtado\Mortgage\Contracts\FeeRulesInterface;
use LBHurtado\Mortgage\ValueObjects\Percent;

class HousingFeeRules extends FeeRules implements FeeRulesInterface
{
    /**
     * Return the multiplier to apply for partial miscellaneous fees.
     *
     * This implementation assumes no partial miscellaneous fee is collected upfront.
     */
    public function getPartialMiscellaneousFeeMultiplier(float $tcp, Percent $percentDp): ?Percent
    {
        return Percent::ofFraction(0); // No upfront MF by default for housing
    }

    public function shouldApplyMiscellaneousFee(float $tcp): bool
    {
        return true;
    }
}
