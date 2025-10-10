<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Models\Cleaner;
use App\Models\Client;
use App\Models\Extra;
use App\Models\Pricing;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_client_can_create_booking(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();
        $extras = Extra::factory()->count(2)->create();

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $pricing = $service->pricings->first();

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'location' => '123 Main St, Springfield',
                    ],
                    'relationships' => [
                        'service' => [
                            'data' => ['id' => $service->id],
                        ],
                        'pricing' => [
                            'data' => ['id' => $pricing->id],
                        ],
                        'extras' => [
                            'data' => $extras->map(fn ($extra) => ['id' => $extra->id])->toArray(),
                        ],
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('bookings', [
            'has_cleaning_material' => true,
            'urgency' => 'flexible',
            'pricing_id' => $pricing->id,
            'selected_amount' => $pricing->amount,
            'location' => '123 Main St, Springfield',
            'scheduled_at' => null,
        ]);
    }

    public function test_client_can_create_booking_with_no_extras(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $pricing = $service->pricings->first();

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'location' => '123 Main St, Springfield',
                    ],
                    'relationships' => [
                        'service' => [
                            'data' => ['id' => $service->id],
                        ],
                        'pricing' => [
                            'data' => ['id' => $pricing->id],
                        ],
                        'extras' => [
                            'data' => [],
                        ],
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('bookings', [
            'has_cleaning_material' => true,
            'urgency' => 'flexible',

            'pricing_id' => $pricing->id,
            'selected_amount' => $pricing->amount,

            'scheduled_at' => null,

            //  TODO: Price calculator action class
            'amount' => $pricing->amount,
            'status' => 'pending',
            'client_id' => $client->id,
            'service_id' => $service->id,
            'location' => '123 Main St, Springfield',
        ]);
    }

    public function test_cleaner_cannot_create_booking(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();
        $extras = Extra::factory()->count(2)->create();

        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $pricing = $service->pricings->first();

        $this->actingAs($cleaner->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'location' => '123 Main St, Springfield',
                    ],
                    'relationships' => [
                        'service' => [
                            'data' => ['id' => $service->id],
                        ],
                        'pricing' => [
                            'data' => ['id' => $pricing->id],
                        ],
                        'extras' => [
                            'data' => $extras->map(fn ($extra) => ['id' => $extra->id])->toArray(),
                        ],
                    ],
                ],
            ])
            ->assertForbidden();
    }

    public function test_client_can_view_own_bookings_index(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);
        $pricing = $service->pricings->first();

        $booking = $client->bookings()->create([
            'service_id' => $service->id,
            'pricing_id' => $pricing->id,
            'selected_amount' => $pricing->amount,
            'urgency' => 'flexible',
            'has_cleaning_material' => true,
            'amount' => $pricing->amount,
            'status' => 'pending',
            'location' => '123 Main St, Springfield',
        ]);

        $this->actingAs($client->user)
            ->getJson(route('api.v1.bookings.index'))
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $booking->id]);
    }

    public function test_client_can_view_own_booking_show(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);
        $pricing = $service->pricings->first();

        $booking = $client->bookings()->create([
            'service_id' => $service->id,
            'pricing_id' => $pricing->id,
            'selected_amount' => $pricing->amount,
            'urgency' => 'flexible',
            'has_cleaning_material' => true,
            'amount' => $pricing->amount,
            'status' => 'pending',
            'location' => '123 Main St, Springfield',
        ]);

        $this->actingAs($client->user)
            ->getJson(route('api.v1.bookings.show', $booking))
            ->assertOk()
            ->assertJsonFragment(['id' => (string) $booking->id]);
    }

    public function test_client_cannot_view_other_clients_booking(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();
        $client1 = Client::factory()->create();
        $client1->user->assignRole(Role::Client);
        $client2 = Client::factory()->create();
        $client2->user->assignRole(Role::Client);
        $pricing = $service->pricings->first();

        $booking = $client1->bookings()->create([
            'service_id' => $service->id,
            'pricing_id' => $pricing->id,
            'selected_amount' => $pricing->amount,
            'urgency' => 'flexible',
            'has_cleaning_material' => true,
            'amount' => $pricing->amount,
            'status' => 'pending',
            'location' => '123 Main St, Springfield',
        ]);

        $this->actingAs($client2->user)
            ->getJson(route('api.v1.bookings.show', $booking))
            ->assertForbidden();
    }
}
