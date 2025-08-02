<?php

namespace Database\Factories;

use App\Enums\MessageType;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'chat_room_id' => ChatRoom::factory(),
            'content' => $this->faker->text(200),
            'type' => $this->faker->randomElement(MessageType::values()),
        ];
    }
}
