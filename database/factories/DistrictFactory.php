<?php

namespace Database\Factories;

use App\Models\City;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\District>
 */
class DistrictFactory extends BaseFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->localized(fn(): string => $this->faker->city()),
            'city_id' => City::factory(),
        ];
    }
}
