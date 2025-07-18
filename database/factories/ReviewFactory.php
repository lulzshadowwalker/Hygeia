<?php

namespace Database\Factories;

use App\Models\Cleaner;
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
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->text(),
            'reviewable_type' => fake()->randomElement([Cleaner::class]),
            'reviewable_id' => Cleaner::factory(),
            'user_id' => User::factory(),
        ];
    }
}
