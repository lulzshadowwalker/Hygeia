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
            'name' => $this->localized(fn(): string => $this->faker->word()),
            'type' => $this->faker->randomElement(ServiceType::values()),
        ];
    }
}
