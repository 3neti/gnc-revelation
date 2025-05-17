<?php

namespace LBHurtado\Mortgage\Rules;

use LBHurtado\Mortgage\Contracts\FeeRulesInterface;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\ValueObjects\Percent;

class FeeRules implements FeeRulesInterface
{
    public function __construct(protected LendingInstitution $lendingInstitution){}

    public function getLendingInstitution(): LendingInstitution
    {
        return $this->lendingInstitution;
    }


    /**
     * Use down payment percentage as the MF multiplier by default.
     */
    public function getPartialMiscellaneousFeeMultiplier(float $tcp, Percent $percentDp): ?Percent
    {
        return $this->getLendingInstitution()?->getPercentDownPayment() ?? null;
    }

    public function shouldApplyMiscellaneousFee(float $tcp): bool
    {
        return false;
    }
}
