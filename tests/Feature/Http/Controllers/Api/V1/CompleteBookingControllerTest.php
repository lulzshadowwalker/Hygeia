<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Cleaner;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class CompleteBookingControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_cleaner_can_complete_a_confirmed_booking(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);
        $this->actingAs($cleaner->user);

        $booking = Booking::factory()->for($cleaner)->create([
            'status' => BookingStatus::Confirmed,
        ]);

        $this->postJson(route('api.v1.bookings.complete', $booking))
            ->assertOk()
            ->assertJsonPath('data.attributes.status', BookingStatus::Completed->value);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Completed,
        ]);

        $booking->refresh();
        $this->assertTrue($booking->status->isCompleted());
    }

    public function test_client_cannot_complete_a_booking(): void
    {
        $booking = Booking::factory()->create(['status' => BookingStatus::Confirmed]);
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);
        $this->actingAs($client->user);

        $this->postJson(route('api.v1.bookings.complete', $booking))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Confirmed,
        ]);

        $booking->refresh();
        $this->assertTrue($booking->status->isConfirmed());
    }

    public function test_cannot_complete_an_already_completed_booking(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);
        $this->actingAs($cleaner->user);

        $booking = Booking::factory()->for($cleaner)->create([
            'status' => BookingStatus::Completed,
        ]);

        $this->postJson(route('api.v1.bookings.complete', $booking))
            ->assertStatus(400)
            ->assertJsonPath('errors.0.title', 'Booking Already Completed');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Completed,
        ]);

        $booking->refresh();
        $this->assertTrue($booking->status->isCompleted());
    }

    public function test_cannot_complete_a_non_confirmed_booking(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);
        $this->actingAs($cleaner->user);

        $booking = Booking::factory()->for($cleaner)->create([
            'status' => BookingStatus::Pending,
        ]);

        $this->postJson(route('api.v1.bookings.complete', $booking))
            ->assertStatus(400)
            ->assertJsonPath('errors.0.title', 'Invalid Booking Status');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Pending,
        ]);

        $booking->refresh();
        $this->assertTrue($booking->status->isPending());
    }
}
