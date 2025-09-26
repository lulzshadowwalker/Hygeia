<?php

namespace Database\Factories;

use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'status' => fake()->randomElement(SupportTicketStatus::values()),
            'user_id' => User::factory(),
        ];
    }
}
