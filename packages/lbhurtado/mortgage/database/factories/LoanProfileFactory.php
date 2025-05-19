<?php

namespace LBHurtadp\Mortgage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Models\LoanProfile;

class LoanProfileFactory extends Factory
{
    protected $model = LoanProfile::class;

    public function definition(): array
    {
        return [
            'lending_institution' => $this->faker->randomElement(LendingInstitution::keys()),
            'total_contract_price' => $this->faker->numberBetween(800_000, 4_000_000),
            'inputs' => $this->faker->rgbColorAsArray(),
            'computation' => $this->faker->rgbColorAsArray(),
            'qualified' => true,
            'required_equity' => 100000,
            'income_gap' => 100000,
            'suggested_down_payment_percent' => '0.13',
            'reason' => $this->faker->sentence,
            'reserved_at' => $this->faker->dateTime,
        ];
    }
}
