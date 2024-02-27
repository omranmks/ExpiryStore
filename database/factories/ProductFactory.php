<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => 1,
            'name' => $this->faker->name(),
            'image' => $this->faker->imageUrl(),
            'description' => $this->faker->text(500),
            'quantity' => rand(10, 50),
            'price' => 0,
            'current_price' => rand(4000, 10000),
            'expiry_date' => $this->faker->dateTimeBetween('+1 week', '+5 week'),
        ];
    }
}
