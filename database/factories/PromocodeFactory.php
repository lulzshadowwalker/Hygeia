<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promocode>
 */
class PromocodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('PROMO###')),
            'discount_percentage' => $this->faker->randomFloat(2, 5, 40),
            'max_discount_amount' => $this->faker->randomFloat(2, 500, 5000),
            'currency' => 'HUF',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'max_global_uses' => $this->faker->optional()->numberBetween(1, 200),
        ];
    }
}
