<?php

namespace Tests\Unit\Models;

use App\Enums\Role;
use App\Models\ChatRoom;
use App\Models\Client;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class MessageTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    /** @test */
    public function it_touches_chat_room_when_message_is_created()
    {
        // Arrange
        $client = Client::factory()->create();
        $user = $client->user;
        $user->assignRole(Role::Client->value);
        $chatRoom = ChatRoom::factory()->create();
        $originalUpdatedAt = $chatRoom->updated_at;

        // Act - travel forward in time to ensure updated_at changes
        $this->travel(1)->second();

        Message::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'content' => 'Test message',
            'type' => 'text',
        ]);

        // Assert
        $chatRoom->refresh();
        $this->assertTrue($chatRoom->updated_at->isAfter($originalUpdatedAt));
    }

    /** @test */
    public function it_touches_chat_room_when_message_is_updated()
    {
        // Arrange
        $client = Client::factory()->create();
        $user = $client->user;
        $user->assignRole(Role::Client->value);
        $chatRoom = ChatRoom::factory()->create();
        $message = Message::factory()->create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
        ]);

        $this->travel(1)->second();
        $originalUpdatedAt = $chatRoom->fresh()->updated_at;

        // Act
        $this->travel(1)->second();
        $message->update(['content' => 'Updated content']);

        // Assert
        $chatRoom->refresh();
        $this->assertTrue($chatRoom->updated_at->isAfter($originalUpdatedAt));
    }

    /** @test */
    public function it_touches_chat_room_when_message_is_deleted()
    {
        // Arrange
        $client = Client::factory()->create();
        $user = $client->user;
        $user->assignRole(Role::Client->value);
        $chatRoom = ChatRoom::factory()->create();
        $message = Message::factory()->create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
        ]);

        $this->travel(1)->second();
        $originalUpdatedAt = $chatRoom->fresh()->updated_at;

        // Act
        $this->travel(1)->second();
        $message->delete();

        // Assert
        $chatRoom->refresh();
        $this->assertTrue($chatRoom->updated_at->isAfter($originalUpdatedAt));
    }
}
