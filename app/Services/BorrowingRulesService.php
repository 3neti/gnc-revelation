<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use App\Exceptions\MinimumBorrowingAgeNotMet;
use App\Exceptions\MaximumBorrowingAgeBreached;

class BorrowingRulesService
{
    public function __construct(
        protected AgeService $ageService
    ) {}

    public function getMinimumAge(): int
    {
        return config('gnc-revelation.limits.min_borrowing_age', 21);
    }

    public function getMaximumAge(): int
    {
        return config('gnc-revelation.limits.max_borrowing_age', 65);
    }

    public function validateBirthdate(Carbon $birthdate): void
    {
        $age = (int) floor($this->ageService->getAgeInFloat($birthdate));

        if ($age < $this->getMinimumAge()) {
            throw new MinimumBorrowingAgeNotMet("Age {$age} is below minimum of {$this->getMinimumAge()}.");
        }

        if ($age > $this->getMaximumAge()) {
            throw new MaximumBorrowingAgeBreached("Age {$age} exceeds maximum of {$this->getMaximumAge()}.");
        }
    }

    public function calculateAge(Carbon $birthdate): float
    {
        return $this->ageService->getAgeInFloat($birthdate);
    }
}
