<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\ChatRoomType;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Cleaner;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class BookingChatRoomControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_client_can_create_a_chat_room_for_a_confirmed_booking(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $booking = Booking::factory()
            ->for($client)
            ->for($cleaner)
            ->confirmed()
            ->create();

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.chat.rooms.store', ['booking' => $booking->id]))
            ->assertCreated();

        $this->assertDatabaseHas('chat_rooms', [
            'booking_id' => $booking->id,
            'type' => ChatRoomType::Standard->value,
        ]);

        $this->assertDatabaseHas('chat_room_participants', [
            'user_id' => $client->user_id,
        ]);

        $this->assertDatabaseHas('chat_room_participants', [
            'user_id' => $cleaner->user_id,
        ]);
    }

    public function test_cleaner_can_create_a_chat_room_for_a_confirmed_booking(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $booking = Booking::factory()
            ->for($client)
            ->for($cleaner)
            ->confirmed()
            ->create();

        $this->actingAs($cleaner->user)
            ->postJson(route('api.v1.bookings.chat.rooms.store', ['booking' => $booking->id]))
            ->assertCreated();

        $this->assertDatabaseHas('chat_rooms', [
            'booking_id' => $booking->id,
            'type' => ChatRoomType::Standard->value,
        ]);

        $this->assertDatabaseHas('chat_room_participants', [
            'user_id' => $client->user_id,
        ]);

        $this->assertDatabaseHas('chat_room_participants', [
            'user_id' => $cleaner->user_id,
        ]);
    }

    public function test_cannot_create_a_chat_room_for_a_non_confirmed_booking(): void
    {
        $statuses = [BookingStatus::Pending, BookingStatus::Completed, BookingStatus::Cancelled];

        foreach ($statuses as $status) {
            $client = Client::factory()->create();
            $client->user->assignRole(Role::Client);

            $cleaner = Cleaner::factory()->create();
            $cleaner->user->assignRole(Role::Cleaner);

            $booking = Booking::factory()
                ->for($client)
                ->for($cleaner)
                ->state(['status' => $status])
                ->create();

            $this->actingAs($client->user)
                ->postJson(route('api.v1.bookings.chat.rooms.store', ['booking' => $booking->id]))
                ->assertForbidden();

            $this->actingAs($cleaner->user)
                ->postJson(route('api.v1.bookings.chat.rooms.store', ['booking' => $booking->id]))
                ->assertForbidden();

            $this->assertDatabaseMissing('chat_rooms', [
                'booking_id' => $booking->id,
            ]);
        }
    }

    public function test_unauthorized_user_cannot_create_a_chat_room(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $booking = Booking::factory()
            ->for($client)
            ->for($cleaner)
            ->confirmed()
            ->create();

        $otherUser = Cleaner::factory()->create()->user;

        $this->actingAs($otherUser)
            ->postJson(route('api.v1.bookings.chat.rooms.store', ['booking' => $booking->id]))
            ->assertForbidden();

        $this->assertDatabaseMissing('chat_rooms', [
            'booking_id' => $booking->id,
        ]);
    }

    public function test_it_does_not_create_duplicate_chat_rooms(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $booking = Booking::factory()
            ->for($client)
            ->for($cleaner)
            ->confirmed()
            ->create();

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.chat.rooms.store', ['booking' => $booking->id]))
            ->assertCreated();

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.chat.rooms.store', ['booking' => $booking->id]))
            ->assertOk();

        $this->assertDatabaseCount('chat_rooms', 1);
        $this->assertDatabaseCount('chat_room_participants', 2);
    }
}
