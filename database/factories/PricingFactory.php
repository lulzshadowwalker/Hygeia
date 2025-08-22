<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pricing>
 */
class PricingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'min_area' => $this->faker->numberBetween(0, 90),
            'max_area' => $this->faker->numberBetween(100, 200),
            'amount' => $this->faker->randomFloat(2, 50, 9980),
            'service_id' => Service::factory(),
        ];
    }
}
