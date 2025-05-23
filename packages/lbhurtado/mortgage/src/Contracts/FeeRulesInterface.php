<?php

namespace LBHurtado\Mortgage\Contracts;

use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\ValueObjects\Percent;

interface FeeRulesInterface
{
    public function getLendingInstitution(): LendingInstitution;

    /**
     * Determine the multiplier for the partial miscellaneous fee.
     *
     * @param float $tcp Total contract price
     * @param Percent $percentDp Down Payment percentage (as a value object)
     * @return Percent|null The multiplier to apply to the total miscellaneous fee
     */
    public function getPartialMiscellaneousFeeMultiplier(float $tcp, Percent $percentDp): ?Percent;

    /**
     * Determine whether miscellaneous fees should be applied.
     *
     * @param  float  $tcp
     * @return bool
     */
    public function shouldApplyMiscellaneousFee(float $tcp): bool;
}
