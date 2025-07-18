<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Favorite;
use App\Models\User;

class FavoriteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Favorite::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'favoriteable_type' => fake()->word(),
            'favoriteable_id' => fake()->numberBetween(-10000, 10000),
            'user_id' => User::factory(),
        ];
    }
}
