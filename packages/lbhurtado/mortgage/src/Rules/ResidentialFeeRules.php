<?php

namespace LBHurtado\Mortgage\Rules;

use LBHurtado\Mortgage\Contracts\FeeRulesInterface;
use LBHurtado\Mortgage\ValueObjects\Percent;

class ResidentialFeeRules extends FeeRules implements FeeRulesInterface
{
    public function shouldApplyMiscellaneousFee(float $tcp): bool
    {
        // For now, always apply MF — can customize this based on TCP brackets later
        return true;
    }
}
