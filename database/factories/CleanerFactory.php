<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Cleaner;
use App\Models\User;

class CleanerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cleaner::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        //  TODO: Better Cleanerfactory
        return [
            'service_area' => fake()->word(),
            'available_days' => '[]',
            'max_hours_per_week' => fake()->numberBetween(1, 45),
            'time_slots' => '[]',
            'years_of_experience' => fake()->numberBetween(0, 20),
            'has_cleaning_supplies' => fake()->boolean(),
            'comfortable_with_pets' => fake()->boolean(),
            'previous_job_types' => '[]',
            'service_radius' => fake()->numberBetween(15, 100),
            'preferred_job_types' => '[]',
            'agreed_to_terms' => fake()->boolean(),
            'user_id' => User::factory(),
        ];
    }
}
