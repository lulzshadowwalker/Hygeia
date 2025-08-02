<?php

namespace Tests\Traits;

use App\Enums\Role;
use App\Models\ChatRoom;
use App\Models\Client;
use App\Models\Message;
use App\Models\User;

trait WithChat
{
    use WithRoles;

    protected User $client;
    protected User $admin;
    protected ChatRoom $chatRoom;

    public function setUpWithChat(): void
    {
        $this->setUpWithRoles();

        // Create a client user
        $this->client = User::factory()->has(Client::factory())->create();
        $this->client->assignRole(Role::Client->value);

        // Create an admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::Admin->value);

        // Create a chat room with both participants
        $this->chatRoom = ChatRoom::factory()->create();
        $this->chatRoom->addParticipant($this->client);
        $this->chatRoom->addParticipant($this->admin);
    }

    protected function createMessageForChatRoom(ChatRoom $chatRoom, User $user, string $content = 'Test message'): Message
    {
        return Message::factory()->create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'content' => $content,
            'type' => 'text',
        ]);
    }

    protected function createChatRoomWithParticipants(array $users): ChatRoom
    {
        $chatRoom = ChatRoom::factory()->create();

        foreach ($users as $user) {
            $chatRoom->addParticipant($user);
        }

        return $chatRoom;
    }
}
