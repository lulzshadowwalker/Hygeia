<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Http\Resources\V1\OfferResource;
use App\Models\Booking;
use App\Models\Cleaner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class OfferControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_lists_offers(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner->value);

        $offers = Booking::factory()->count(5)->create(['status' => BookingStatus::Pending->value]);
        $resource = OfferResource::collection($offers);

        $this->actingAs($cleaner->user)
            ->getJson(route('api.v1.offers.index'))
            ->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_only_lists_bookings_with_status_pending_as_offers(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner->value);

        Booking::factory()->count(3)->create(['status' => BookingStatus::Pending->value]);
        Booking::factory()->count(2)->create(['status' => BookingStatus::Completed->value]);
        Booking::factory()->count(4)->create(['status' => BookingStatus::Cancelled->value]);

        $this->actingAs($cleaner->user)
            ->getJson(route('api.v1.offers.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_it_shows_an_offer(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner->value);

        $offer = Booking::factory()->create(['status' => BookingStatus::Pending->value]);
        $resource = OfferResource::make($offer);

        $this->actingAs($cleaner->user)
            ->getJson(route('api.v1.offers.show', $offer))
            ->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_does_not_show_a_booking_if_not_pending_as_offer(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner->value);

        $offer = Booking::factory()->create(['status' => BookingStatus::Completed->value]);

        $this->actingAs($cleaner->user)
            ->getJson(route('api.v1.offers.show', $offer))
            ->assertNotFound();
    }
}
