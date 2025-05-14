<?php

namespace LBHurtadp\Mortgage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LBHurtado\Mortgage\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->ean8(),
            'name' => $this->faker->word(),
            'brand' => $this->faker->word(),
            'category' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(800_000, 4_000_000),
        ];
    }
}
