<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\ChatRoomType;
use App\Enums\Role;
use App\Models\ChatRoom;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\WithChat;

class ChatRoomControllerTest extends TestCase
{
    use RefreshDatabase, WithChat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWithChat();
    }

    public function test_it_returns_all_chat_rooms_for_authenticated_user(): void
    {
        $this->actingAs($this->client);

        // Create additional chat room for this user
        $anotherChatRoom = ChatRoom::factory()->create();
        $anotherChatRoom->addParticipant($this->client);

        $expectedChatRooms = $this->client->chatRooms()
            ->with(['latestMessage.user', 'participants'])
            ->orderBy('updatedAt', 'desc')
            ->get();

        $response = $this->getJson(route('api.v1.chat.rooms.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'type',
                            'createdAt',
                            'updatedAt',
                        ],
                        'relationships' => [
                            'participants',
                        ]
                    ]
                ]
            ])
            ->assertJsonCount($expectedChatRooms->count(), 'data');
    }

    public function test_it_returns_empty_array_when_user_has_no_chat_rooms(): void
    {
        $newUser = User::factory()->has(Client::factory())->create();
        $newUser->assignRole(Role::Client->value);
        $this->actingAs($newUser);

        $response = $this->getJson(route('api.v1.chat.rooms.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => []
            ]);
    }

    public function test_it_shows_a_specific_chat_room_for_participant(): void
    {
        $this->actingAs($this->client);

        $response = $this->getJson(route('api.v1.chat.rooms.show', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'type',
                        'createdAt',
                        'updatedAt',
                    ],
                    'relationships' => [
                        'participants' => [
                            '*' => [
                                'id',
                                'type',
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->chatRoom->id,
                    'type' => 'chat-room',
                ]
            ]);
    }

    public function test_it_denies_access_to_chat_room_for_non_participant(): void
    {
        $nonParticipant = User::factory()->has(Client::factory())->create();
        $nonParticipant->assignRole(Role::Client->value);
        $this->actingAs($nonParticipant);

        $response = $this->getJson(route('api.v1.chat.rooms.show', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'message' => 'You are not a participant of this chat room.'
            ]);
    }

    public function test_it_creates_a_new_chat_room(): void
    {
        $this->actingAs($this->client);

        $otherUser = User::factory()->has(Client::factory())->create();
        $otherUser->assignRole(Role::Client->value);

        $chatRoomData = [
            'data' => [
                'relationships' => [
                    'participants' => [
                        [
                            'id' => $otherUser->id
                        ],
                        [
                            'id' => $this->client->id
                        ],
                    ]
                ]
            ]
        ];

        $response = $this->postJson(route('api.v1.chat.rooms.store'), $chatRoomData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'type',
                        'createdAt',
                        'updatedAt',
                    ],
                    'relationships' => [
                        'participants'
                    ]
                ]
            ]);

        // Assert the chat room was created in database
        $chatRoomId = $response->json('data.id');
        $this->assertDatabaseHas('chat_rooms', [
            'type' => ChatRoomType::Standard->value,
            'id' => $chatRoomId,
        ]);

        // Assert the user is a participant
        $this->assertDatabaseHas('chat_room_participants', [
            'chat_room_id' => $chatRoomId,
            'user_id' => $this->client->id
        ]);
    }

    public function test_it_allows_user_to_join_chat_room(): void
    {
        $newUser = User::factory()->has(Client::factory())->create();
        $newUser->assignRole(Role::Client->value);
        $this->actingAs($newUser);

        $response = $this->postJson(route('api.v1.chat.rooms.join', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'type',
                    ],
                    'relationships' => [
                        'participants'
                    ]
                ]
            ]);

        // Assert user is now a participant
        $this->assertDatabaseHas('chat_room_participants', [
            'chat_room_id' => $this->chatRoom->id,
            'user_id' => $newUser->id
        ]);
    }

    public function test_it_handles_joining_chat_room_when_already_participant(): void
    {
        $this->actingAs($this->client);

        $response = $this->postJson(route('api.v1.chat.rooms.join', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_CONFLICT);

        // Assert user is still a participant (no duplicates)
        $participantCount = $this->chatRoom->participants()->where('user_id', $this->client->id)->count();
        $this->assertEquals(1, $participantCount);
    }

    public function test_it_allows_user_to_leave_chat_room(): void
    {
        $this->actingAs($this->client);

        $response = $this->deleteJson(route('api.v1.chat.rooms.leave', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        // Assert user is no longer a participant
        $this->assertDatabaseMissing('chat_room_participants', [
            'chat_room_id' => $this->chatRoom->id,
            'user_id' => $this->client->id,
        ]);
    }

    public function test_it_handles_leaving_chat_room_when_not_participant(): void
    {
        $nonParticipant = User::factory()->has(Client::factory())->create();
        $nonParticipant->assignRole(Role::Client->value);
        $this->actingAs($nonParticipant);

        $response = $this->deleteJson(route('api.v1.chat.rooms.leave', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Successfully left chat room'
            ]);
    }

    public function test_it_returns_existing_support_chat_room(): void
    {
        $client = Client::factory()->create();

        // Create a support chat room with client participant
        $supportChatRoom = ChatRoom::factory()->create([
            'type' => ChatRoomType::Support->value,
        ]);
        $supportChatRoom->addParticipant($client->user);

        $this->actingAs($client->user);

        $response = $this->getJson(route('api.v1.chat.rooms.support'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'type',
                        'createdAt',
                        'updatedAt',
                    ],
                    'relationships' => [
                        'participants'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => (string) $supportChatRoom->id,
                    'type' => 'chat-room',
                    'attributes' => [
                        'type' => 'support'
                    ]
                ]
            ]);
    }

    public function test_it_creates_support_chat_room_when_none_exists(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($client->user);

        $response = $this->getJson(route('api.v1.chat.rooms.support'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'type',
                        'createdAt',
                        'updatedAt',
                    ],
                    'relationships' => [
                        'participants'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'type' => 'chat-room',
                    'attributes' => [
                        'type' => 'support',
                    ]
                ]
            ]);

        // Assert the support chat room was created in database
        $this->assertDatabaseHas('chat_rooms', [
            'type' => ChatRoomType::Support->value,
        ]);
        $this->assertDatabaseHas('chat_room_participants', [
            'chat_room_id' => $response->json('data.id'),
            'user_id' => $client->user->id
        ]);
    }
}
