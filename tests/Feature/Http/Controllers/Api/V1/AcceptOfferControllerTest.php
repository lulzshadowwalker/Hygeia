<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Cleaner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class AcceptOfferControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_cleaner_can_accept_booking(): void
    {
        $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);
        $this->actingAs($cleaner->user);

        $this->postJson(route('api.v1.offers.accept', $booking))
            ->assertOk()
            ->assertJsonPath('data.attributes.status', BookingStatus::Confirmed->value);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Confirmed,
            'cleaner_id' => $cleaner->id,
        ]);

        $booking->refresh();
        $this->assertTrue($booking->status->isConfirmed());
        $this->assertEquals($cleaner->id, $booking->cleaner_id);
        $this->assertCount(1, $cleaner->bookings);
        $this->assertNotNull($booking->cleaner);
    }
}
