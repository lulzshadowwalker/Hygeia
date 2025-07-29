<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Events\MessageSent;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\WithChat;

class ChatMessageControllerTest extends TestCase
{
    use RefreshDatabase, WithChat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWithChat();
    }

    public function test_it_returns_messages_for_a_chat_room(): void
    {
        $this->actingAs($this->client);

        // Create some messages
        $message1 = $this->createMessageForChatRoom($this->chatRoom, $this->client, 'First message');
        $message2 = $this->createMessageForChatRoom($this->chatRoom, $this->admin, 'Second message');
        $message3 = $this->createMessageForChatRoom($this->chatRoom, $this->client, 'Third message');

        $response = $this->getJson(route('api.v1.chat.rooms.messages.index', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'content',
                            'type',
                            'created_at'
                        ]
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_it_denies_access_to_messages_for_non_participant(): void
    {
        $nonParticipant = User::factory()->has(Client::factory())->create();
        $nonParticipant->assignRole(Role::Client->value);
        $this->actingAs($nonParticipant);

        $response = $this->getJson(route('api.v1.chat.rooms.messages.index', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'message' => 'Access denied'
            ]);
    }

    public function test_it_sends_a_message_to_chat_room(): void
    {
        Event::fake();
        $this->actingAs($this->client);

        $messageContent = 'Hello, this is a test message!';

        $response = $this->postJson(route('api.v1.chat.rooms.messages.store', ['chatRoom' => $this->chatRoom]), [
            'content' => $messageContent,
            'type' => 'text'
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'content',
                        'type',
                        'created_at'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'type' => 'message',
                    'attributes' => [
                        'content' => $messageContent,
                        'type' => 'text'
                    ]
                ]
            ]);

        // Assert message was created in database
        $this->assertDatabaseHas('messages', [
            'content' => $messageContent,
            'type' => 'text',
            'user_id' => $this->client->id,
            'chat_room_id' => $this->chatRoom->id
        ]);

        // Assert event was dispatched
        Event::assertDispatched(MessageSent::class);
    }

    public function test_it_validates_message_content_when_sending(): void
    {
        $this->actingAs($this->client);

        $response = $this->postJson(route('api.v1.chat.rooms.messages.store', ['chatRoom' => $this->chatRoom]), [
            'content' => '', // Empty content
            'type' => 'text'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_it_validates_message_type_when_sending(): void
    {
        $this->actingAs($this->client);

        $response = $this->postJson(route('api.v1.chat.rooms.messages.store', ['chatRoom' => $this->chatRoom]), [
            'content' => 'Test message',
            'type' => 'invalid_type'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_it_denies_sending_message_to_non_participant(): void
    {
        $nonParticipant = User::factory()->has(Client::factory())->create();
        $nonParticipant->assignRole(Role::Client->value);
        $this->actingAs($nonParticipant);

        $response = $this->postJson(route('api.v1.chat.rooms.messages.store', ['chatRoom' => $this->chatRoom]), [
            'content' => 'Test message',
            'type' => 'text'
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'message' => 'Access denied'
            ]);
    }
}
