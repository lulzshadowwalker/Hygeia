<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Cleaner;
use App\Models\Client;
use App\Models\Extra;
use App\Models\Pricing;
use App\Models\Promocode;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

        $image1 = UploadedFile::fake()->image('photo-1.jpg');
        $image2 = UploadedFile::fake()->image('photo-2.jpg');

        $response = $this->actingAs($client->user)
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
                        'images' => [
                            $image1,
                            $image2,
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
                            'data' => $extras
                                ->map(fn ($extra) => ['id' => $extra->id])
                                ->toArray(),
                        ],
                    ],
                ],
            ])
            ->assertCreated();

        $pricingAmount = (float) $pricing->getRawOriginal('amount');
        $extrasTotal = $extras->sum(fn (Extra $extra): float => (float) $extra->getRawOriginal('amount'));

        $this->assertDatabaseHas('bookings', [
            'has_cleaning_material' => true,
            'urgency' => 'flexible',
            'pricing_id' => $pricing->id,
            'selected_amount' => number_format($pricingAmount, 2, '.', ''),
            'amount' => number_format($pricingAmount + $extrasTotal, 2, '.', ''),
            'currency' => 'HUF',
            'location' => '123 Main St, Springfield',
            'lat' => 40.712776,
            'lng' => -74.005974,
            'scheduled_at' => null,
        ]);

        $booking = $client->bookings()->first();
        $this->assertNotNull($booking->images);
        $this->assertNotEmpty($booking->images);
        $this->assertCount(2, $booking->images);
        $this->assertSame('HUF', $booking->currency);

        foreach ($extras as $extra) {
            $this->assertDatabaseHas('booking_extra', [
                'booking_id' => $booking->id,
                'extra_id' => $extra->id,
                'amount' => $extra->getRawOriginal('amount'),
                'currency' => 'HUF',
            ]);
        }

        $response
            ->assertJsonPath('data.attributes.currency', 'HUF');
    }

    public function test_client_can_create_booking_with_no_extras(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $pricing = $service->pricings->first();

        $response = $this->actingAs($client->user)
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
            ->assertCreated();

        $pricingAmount = (float) $pricing->getRawOriginal('amount');

        $this->assertDatabaseHas('bookings', [
            'has_cleaning_material' => true,
            'urgency' => 'flexible',
            'pricing_id' => $pricing->id,
            'selected_amount' => number_format($pricingAmount, 2, '.', ''),
            'scheduled_at' => null,
            'amount' => number_format($pricingAmount, 2, '.', ''),
            'currency' => 'HUF',
            'status' => 'pending',
            'client_id' => $client->id,
            'service_id' => $service->id,
            'location' => '123 Main St, Springfield',
            'lat' => 40.712776,
            'lng' => -74.005974,
        ]);

        $response
            ->assertJsonPath('data.attributes.currency', 'HUF');
    }

    public function test_client_can_create_booking_with_valid_promocode(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);
        $pricing = $service->pricings->first();

        $promocode = Promocode::factory()->create([
            'code' => 'SAVE20',
            'discount_percentage' => 20,
            'max_discount_amount' => 1000,
            'currency' => 'HUF',
        ]);

        $pricingAmount = (float) $pricing->getRawOriginal('amount');
        $expectedDiscount = min($pricingAmount * 0.2, 1000);
        $expectedTotal = $pricingAmount - $expectedDiscount;

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'promocode' => 'save20',
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
            ->assertJsonPath('data.attributes.discountAmount', number_format($expectedDiscount, 2, '.', ''));

        $this->assertDatabaseHas('bookings', [
            'client_id' => $client->id,
            'promocode_id' => $promocode->id,
            'amount' => number_format($expectedTotal, 2, '.', ''),
        ]);
    }

    public function test_client_cannot_create_booking_with_invalid_promocode(): void
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
                        'promocode' => 'DOESNOTEXIST',
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
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.reason', 'not_found');
    }

    public function test_client_cannot_create_booking_when_promocode_global_cap_is_reached(): void
    {
        $service = Service::factory()->has(Pricing::factory())->create();
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);
        $pricing = $service->pricings->first();

        $promocode = Promocode::factory()->create([
            'max_global_uses' => 1,
        ]);

        Booking::factory()->create([
            'promocode_id' => $promocode->id,
            'status' => BookingStatus::Pending->value,
        ]);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.store'), [
                'data' => [
                    'attributes' => [
                        'hasCleaningMaterials' => true,
                        'urgency' => 'flexible',
                        'promocode' => $promocode->code,
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
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.reason', 'usage_limit_reached');
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
                            'data' => $extras
                                ->map(fn ($extra) => ['id' => $extra->id])
                                ->toArray(),
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
        $pricingAmount = $pricing->getRawOriginal('amount');

        $booking = $client->bookings()->create([
            'service_id' => $service->id,
            'pricing_id' => $pricing->id,
            'selected_amount' => $pricingAmount,
            'urgency' => 'flexible',
            'has_cleaning_material' => true,
            'amount' => $pricingAmount,
            'status' => 'pending',
            'location' => '123 Main St, Springfield',
            'lat' => 40.712776,
            'lng' => -74.005974,
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
        $pricingAmount = $pricing->getRawOriginal('amount');

        $booking = $client->bookings()->create([
            'service_id' => $service->id,
            'pricing_id' => $pricing->id,
            'selected_amount' => $pricingAmount,
            'urgency' => 'flexible',
            'has_cleaning_material' => true,
            'amount' => $pricingAmount,
            'status' => 'pending',
            'location' => '123 Main St, Springfield',
            'lat' => 40.712776,
            'lng' => -74.005974,
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
        $pricingAmount = $pricing->getRawOriginal('amount');

        $booking = $client1->bookings()->create([
            'service_id' => $service->id,
            'pricing_id' => $pricing->id,
            'selected_amount' => $pricingAmount,
            'urgency' => 'flexible',
            'has_cleaning_material' => true,
            'amount' => $pricingAmount,
            'status' => 'pending',
            'location' => '123 Main St, Springfield',
            'lat' => 40.712776,
            'lng' => -74.005974,
        ]);

        $this->actingAs($client2->user)
            ->getJson(route('api.v1.bookings.show', $booking))
            ->assertForbidden();
    }
}
