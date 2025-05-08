<?php

namespace LBHurtadp\Mortgage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LBHurtado\Mortgage\Models\Property;

class PropertyFactory extends Factory
{
    protected $model = Property::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->ean8(),
            'name' => $this->faker->name(),
            'type' => $this->faker->randomElement(['condo', 'type']),
            'cluster' => $this->faker->word(),
            'status' => $this->faker->randomElement(['active', 'inactive'])
        ];
    }
}
