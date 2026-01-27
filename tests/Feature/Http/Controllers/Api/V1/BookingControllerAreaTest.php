<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Enums\ServiceType;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class BookingControllerAreaTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_client_can_create_booking_for_residential_service_with_area(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Residential,
            'price_per_meter' => 100,
        ]);

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'area' => 50, // 50 sqm
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
                            'data' => null, // No pricing ID needed
                        ],
                        'extras' => [
                            'data' => [],
                        ],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.attributes.amount', 5000) // 50 * 100
            ->assertJsonPath('data.attributes.area', 50)
            ->assertJsonPath('data.attributes.pricePerMeter', '100.00');

        $this->assertDatabaseHas('bookings', [
            'service_id' => $service->id,
            'area' => 50,
            'price_per_meter' => 100,
            'amount' => 5000,
        ]);
    }

    public function test_client_cannot_create_booking_for_residential_service_without_area(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Residential,
            'price_per_meter' => 100,
        ]);

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        // 'area' => 50, // Missing area
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
                            'data' => null,
                        ],
                        'extras' => [
                            'data' => [],
                        ],
                    ],
                ],
            ])
            ->assertJsonValidationErrors(['data.attributes.area']);
    }
}
