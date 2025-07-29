<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\ChatRoomRole;
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
        $anotherChatRoom->addParticipant($this->client, ChatRoomRole::Member);

        $expectedChatRooms = $this->client->chatRooms()
            ->with(['latestMessage.user', 'participants'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $response = $this->getJson(route('api.v1.chat.rooms.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                            'created_at',
                            'updated_at',
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
                        'name',
                        'created_at',
                        'updated_at',
                    ],
                    'relationships' => [
                        'participants' => [
                            'data' => [
                                '*' => [
                                    'type',
                                    'id',
                                    'attributes' => [
                                        'name',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->chatRoom->id,
                    'type' => 'chat_room',
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
                'message' => 'Access denied'
            ]);
    }

    public function test_it_creates_a_new_chat_room(): void
    {
        $this->actingAs($this->client);

        $otherUser = User::factory()->has(Client::factory())->create();
        $otherUser->assignRole(Role::Client->value);

        $chatRoomData = [
            'name' => 'Test Chat Room',
            'description' => 'Test description',
            'participant_ids' => [$this->client->id, $otherUser->id]
        ];

        $response = $this->postJson(route('api.v1.chat.rooms.store'), $chatRoomData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                        'created_at',
                        'updated_at',
                    ],
                    'relationships' => [
                        'participants'
                    ]
                ]
            ]);

        // Assert the chat room was created in database
        $chatRoomId = $response->json('data.id');
        $this->assertDatabaseHas('chat_rooms', [
            'id' => $chatRoomId,
            'name' => 'Test Chat Room'
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
                'message',
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                    ],
                    'relationships' => [
                        'participants'
                    ]
                ]
            ])
            ->assertJson([
                'message' => 'Successfully joined chat room'
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

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'User is already a participant'
            ]);

        // Assert user is still a participant (no duplicates)
        $participantCount = $this->chatRoom->participants()->where('user_id', $this->client->id)->count();
        $this->assertEquals(1, $participantCount);
    }

    public function test_it_allows_user_to_leave_chat_room(): void
    {
        $this->actingAs($this->client);

        $response = $this->deleteJson(route('api.v1.chat.rooms.leave', ['chatRoom' => $this->chatRoom]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Successfully left chat room'
            ]);

        // Assert user is no longer a participant
        $this->assertDatabaseMissing('chat_room_participants', [
            'chat_room_id' => $this->chatRoom->id,
            'user_id' => $this->client->id,
            'role' => ChatRoomRole::Member->value
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
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin->value);

        // Create a support chat room with admin participant
        $supportChatRoom = ChatRoom::factory()->create([
            'name' => 'Support',
            'description' => 'Support chat room'
        ]);
        $supportChatRoom->addParticipant($admin, ChatRoomRole::Admin);

        $this->actingAs($this->client);

        $response = $this->getJson(route('api.v1.chat.rooms.support'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                        'created_at',
                        'updated_at',
                    ],
                    'relationships' => [
                        'participants'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $supportChatRoom->id,
                    'type' => 'chat_room',
                    'attributes' => [
                        'name' => 'Support'
                    ]
                ]
            ]);
    }

    public function test_it_creates_support_chat_room_when_none_exists(): void
    {
        $this->actingAs($this->client);

        // Ensure no support chat room with admin participants exists initially
        $supportChatRoomsWithAdmins = ChatRoom::whereHas('participants', function ($query) {
            $query->where('role', ChatRoomRole::Admin);
        })->count();
        
        $this->assertEquals(0, $supportChatRoomsWithAdmins);

        $response = $this->getJson(route('api.v1.chat.rooms.support'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                        'created_at',
                        'updated_at',
                    ],
                    'relationships' => [
                        'participants'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'type' => 'chat_room',
                    'attributes' => [
                        'name' => 'Support'
                    ]
                ]
            ]);

        // Assert the support chat room was created in database
        $this->assertDatabaseHas('chat_rooms', [
            'name' => 'Support',
            'description' => 'Support chat room'
        ]);
    }
}
