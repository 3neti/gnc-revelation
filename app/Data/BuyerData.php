<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class BuyerData extends Data
{
    public int $age;

    public function __construct(
        public Carbon $birthdate,
        public float $gross_monthly_income,
        public float $desired_tcp,
        public float $desired_percent_dp = 0.10,
        public int $preferred_term_years = 30,
    ) {
        $this->age = $birthdate->age;
    }
}
