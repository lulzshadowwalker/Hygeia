<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Review;
use App\Models\User;

class ReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'rating' => fake()->numberBetween(-10000, 10000),
            'comment' => fake()->text(),
            'reviewable_type' => fake()->word(),
            'reviewable_id' => fake()->numberBetween(-10000, 10000),
            'user_id' => User::factory(),
        ];
    }
}
