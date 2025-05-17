<?php

namespace LBHurtado\Mortgage\Factories;

use LBHurtado\Mortgage\Rules\{FeeRules, HousingFeeRules, ResidentialFeeRules};
use LBHurtado\Mortgage\Contracts\FeeRulesInterface;
use LBHurtado\Mortgage\Classes\LendingInstitution;

class FeeRulesFactory
{
    public static function make(LendingInstitution $institution): FeeRulesInterface
    {
        return match ($institution->key()) {
            'hdmf' => new HousingFeeRules($institution),
            'rcbc', 'cbc' => new ResidentialFeeRules($institution),
            default => new FeeRules($institution), // fallback default rule
        };
    }
}
