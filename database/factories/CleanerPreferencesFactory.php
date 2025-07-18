<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Cleaner;
use App\Models\CleanerPreferences;

class CleanerPreferencesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CleanerPreferences::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'language' => fake()->randomElement(["en","hu"]),
            'email_notifications' => fake()->boolean(),
            'push_notifications' => fake()->boolean(),
            'cleaner_id' => Cleaner::factory(),
        ];
    }
}
