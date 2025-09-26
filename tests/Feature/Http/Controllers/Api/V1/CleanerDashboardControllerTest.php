<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Cleaner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class CleanerDashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_cleaner_can_view_dashboard(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);
        $this->actingAs($cleaner->user);

        $offers = Booking::factory()->count(2)->create(['status' => BookingStatus::Pending]);
        $upcomingBookings = Booking::factory()->count(3)->for($cleaner)->create(['status' => BookingStatus::Confirmed]);
        $completedBookings = Booking::factory()->count(3)->for($cleaner)->create(['status' => BookingStatus::Completed]);

        $this->getJson(route('api.v1.profile.dashboard'))
            ->assertOk()
            ->assertJsonPath('data.type', 'cleaner-dashboard')
            ->assertJsonPath('data.id', (string) $cleaner->id)
            ->assertJsonPath('data.attributes.completedBookingsCount', 3)
            ->assertJsonPath('data.attributes.upcomingBookingsCount', 3)
            ->assertJsonPath('data.attributes.averageRating', null)
            ->assertJsonPath('data.attributes.totalReviews', 0)
            ->assertJsonPath('data.attributes.earnings', '4.5')
            ->assertJsonPath('data.attributes.availableDays', $cleaner->available_days)
            ->assertJsonCount(2, 'data.includes.upcomingBookings')
            ->assertJsonCount(2, 'data.includes.offers');
    }
}
