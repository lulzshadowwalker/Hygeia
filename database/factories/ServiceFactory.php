<?php

namespace Database\Factories;

use App\Enums\ServiceType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends BaseFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->localized(fn (): string => $this->faker->word()),
            'type' => ServiceType::Commercial,
            'price_per_meter' => null,
        ];
    }

    public function residential(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => ServiceType::Residential,
            'price_per_meter' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }
}
