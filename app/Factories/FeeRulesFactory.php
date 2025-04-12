<?php

namespace App\Factories;

use App\Contracts\FeeRulesInterface;
use App\Classes\LendingInstitution;
use App\Rules\ResidentialFeeRules;
use App\Rules\HousingFeeRules;
use App\Rules\FeeRules;

class FeeRulesFactory
{
    public static function make(LendingInstitution $institution): FeeRulesInterface
    {
        return match ($institution->key()) {
            'hdmf' => new HousingFeeRules(),
            'rcbc' => new ResidentialFeeRules(),
            default => new FeeRules(), // fallback default rule
        };
    }
}
