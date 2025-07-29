<?php

namespace Database\Seeders;

use App\Enums\ChatRoomRole;
use App\Enums\Role;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing users
        $admin = User::whereHas('roles', fn($q) => $q->where('name', Role::Admin->value))->first();
        $client = User::whereHas('roles', fn($q) => $q->where('name', Role::Client->value))->first();
        $cleaner = User::whereHas('roles', fn($q) => $q->where('name', Role::Cleaner->value))->first();

        if (!$admin || !$client || !$cleaner) {
            $this->command->warn('Make sure to run DatabaseSeeder first to create users with roles');
            return;
        }

        // Create a chat room between client and admin
        $supportRoom = ChatRoom::create();
        $supportRoom->addParticipant($client, ChatRoomRole::Member);
        $supportRoom->addParticipant($admin, ChatRoomRole::Admin);

        // Add some sample messages
        Message::create([
            'chat_room_id' => $supportRoom->id,
            'user_id' => $client->id,
            'content' => 'Hello, I need help with my account.',
            'type' => 'text',
        ]);

        Message::create([
            'chat_room_id' => $supportRoom->id,
            'user_id' => $admin->id,
            'content' => 'Hi! I\'m here to help. What seems to be the issue?',
            'type' => 'text',
        ]);

        Message::create([
            'chat_room_id' => $supportRoom->id,
            'user_id' => $client->id,
            'content' => 'I can\'t see my recent cleaning requests.',
            'type' => 'text',
        ]);

        // Create a chat room between client and cleaner
        $serviceRoom = ChatRoom::create();
        $serviceRoom->addParticipant($client, ChatRoomRole::Member);
        $serviceRoom->addParticipant($cleaner, ChatRoomRole::Member);

        Message::create([
            'chat_room_id' => $serviceRoom->id,
            'user_id' => $client->id,
            'content' => 'Hi! Are you available for a cleaning service tomorrow?',
            'type' => 'text',
        ]);

        Message::create([
            'chat_room_id' => $serviceRoom->id,
            'user_id' => $cleaner->id,
            'content' => 'Hello! Yes, I\'m available. What time works best for you?',
            'type' => 'text',
        ]);

        // Create an empty room for testing
        $emptyRoom = ChatRoom::create();
        $emptyRoom->addParticipant($admin, ChatRoomRole::Admin);

        $this->command->info('Chat rooms and messages created successfully!');
        $this->command->info("Support Room ID: {$supportRoom->id}");
        $this->command->info("Service Room ID: {$serviceRoom->id}");
        $this->command->info("Empty Room ID: {$emptyRoom->id}");
    }
}
