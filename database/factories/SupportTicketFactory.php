<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SupportTicket::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'number' => fake()->word(),
            'subject' => fake()->word(),
            'message' => fake()->text(),
            'status' => fake()->randomElement(["open","in_progress","resolved"]),
            'user_id' => User::factory(),
        ];
    }
}
