<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => rand(1,10),
            'store_id' => 1,
            'title' => $this->faker->sentence(),
            'body' => $this->faker->text(365),
            'rate' => $this->faker->numberBetween(0,5)
        ];
    }
}
