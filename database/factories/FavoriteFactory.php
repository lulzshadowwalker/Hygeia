<?php

namespace Database\Factories;

use App\Models\Cleaner;
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
            'favoriteable_type' => Cleaner::class,
            'favoriteable_id' => Cleaner::facotry(),
            'user_id' => User::factory(),
        ];
    }
}
