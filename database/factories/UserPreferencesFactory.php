<?php

namespace Database\Factories;

use App\Enums\Language;
use App\Models\User;
use App\Models\UserPreferences;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPreferencesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPreferences::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'language' => fake()->randomElement(Language::values()),
            'email_notifications' => fake()->boolean(),
            'push_notifications' => fake()->boolean(),
            'user_id' => User::factory(),
        ];
    }
}
