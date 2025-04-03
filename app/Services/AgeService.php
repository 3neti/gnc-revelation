<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class AgeService
{
    public function getAgeInYears(Carbon $birthdate): int
    {
        return $birthdate->diffInYears(now());
    }

    public function getAgeInFloat(Carbon $birthdate): float
    {
        return round($birthdate->diffInDays(now()) / 365.25, 1);
    }

    public function getYearsUntilAge(Carbon $birthdate, int $targetAge): int
    {
        $age = $this->getAgeInYears($birthdate);
        return max(0, $targetAge - $age);
    }

    public function willReachAgeWithinTerm(Carbon $birthdate, int $ageLimit, int $term): bool
    {
        return $this->getAgeInYears($birthdate) + $term <= $ageLimit;
    }
}
