<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Pricing;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class BookingControllerAreaTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_client_can_create_booking_for_residential_service_with_pricing_tier(): void
    {
        $service = Service::factory()->residential()->create();
        $pricing = Pricing::factory()->for($service)->create([
            'amount' => 4200,
            'min_area' => 0,
            'max_area' => 80,
        ]);

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'location' => [
                            'description' => '123 Main St, Springfield',
                            'lat' => 40.712776,
                            'lng' => -74.005974,
                        ],
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
            ->assertCreated()
            ->assertJsonPath('data.attributes.amount', '4200.00')
            ->assertJsonPath('data.attributes.selectedAmount', '4200.00')
            ->assertJsonPath('data.attributes.currency', 'HUF')
            ->assertJsonPath('data.attributes.area', 0)
            ->assertJsonPath('data.attributes.pricePerMeter', '');

        $this->assertDatabaseHas('bookings', [
            'service_id' => $service->id,
            'pricing_id' => $pricing->id,
            'area' => null,
            'price_per_meter' => null,
            'selected_amount' => '4200.00',
            'amount' => '4200.00',
            'currency' => 'HUF',
        ]);
    }

    public function test_client_cannot_create_booking_for_residential_service_without_pricing(): void
    {
        $service = Service::factory()->residential()->create();

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'location' => [
                            'description' => '123 Main St, Springfield',
                            'lat' => 40.712776,
                            'lng' => -74.005974,
                        ],
                    ],
                    'relationships' => [
                        'service' => [
                            'data' => ['id' => $service->id],
                        ],
                        'extras' => [
                            'data' => [],
                        ],
                    ],
                ],
            ])
            ->assertJsonValidationErrors(['data.relationships.pricing.data.id']);
    }

    public function test_client_cannot_create_booking_for_residential_service_with_area_payload(): void
    {
        $service = Service::factory()->residential()->create();
        $pricing = Pricing::factory()->for($service)->create([
            'amount' => 3600,
        ]);

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'area' => 30,
                    ],
                    'relationships' => [
                        'service' => [
                            'data' => ['id' => $service->id],
                        ],
                        'pricing' => [
                            'data' => ['id' => $pricing->id],
                        ],
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.attributes.area']);
    }
}
