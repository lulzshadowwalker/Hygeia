<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Enums\ServiceType;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Extra;
use App\Models\Pricing;
use App\Models\Promocode;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class PromocodeValidationControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_client_can_validate_promocode_and_receive_pricing_breakdown(): void
    {
        [$client, $service, $pricing, $extras] = $this->prepareBookingData();

        $promocode = Promocode::factory()->create([
            'code' => 'PROMO10',
            'discount_percentage' => 10,
            'max_discount_amount' => 300,
            'currency' => 'HUF',
        ]);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.promocodes.validate'), $this->payload(
                serviceId: $service->id,
                pricingId: $pricing->id,
                extraIds: $extras->pluck('id')->all(),
                code: $promocode->code,
            ))
            ->assertOk()
            ->assertJsonPath('data.attributes.valid', true)
            ->assertJsonPath('data.attributes.reason', null)
            ->assertJsonPath('data.attributes.pricing.selectedAmount', '3000.00')
            ->assertJsonPath('data.attributes.pricing.extrasAmount', '200.00')
            ->assertJsonPath('data.attributes.pricing.discountAmount', '300.00')
            ->assertJsonPath('data.attributes.pricing.totalAmount', '2900.00')
            ->assertJsonPath('data.attributes.pricing.currency', 'HUF')
            ->assertJsonPath('data.includes.promocode.attributes.code', 'PROMO10');
    }

    public function test_validation_returns_not_found_for_unknown_promocode(): void
    {
        [$client, $service, $pricing] = $this->prepareBookingData();

        $this->actingAs($client->user)
            ->postJson(route('api.v1.promocodes.validate'), $this->payload(
                serviceId: $service->id,
                pricingId: $pricing->id,
                extraIds: [],
                code: 'UNKNOWN',
            ))
            ->assertOk()
            ->assertJsonPath('data.attributes.valid', false)
            ->assertJsonPath('data.attributes.reason', 'not_found');
    }

    public function test_validation_returns_usage_limit_reached_when_global_cap_is_hit(): void
    {
        [$client, $service, $pricing] = $this->prepareBookingData();
        $promocode = Promocode::factory()->create([
            'max_global_uses' => 1,
        ]);

        Booking::factory()->create([
            'promocode_id' => $promocode->id,
            'status' => BookingStatus::Pending->value,
        ]);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.promocodes.validate'), $this->payload(
                serviceId: $service->id,
                pricingId: $pricing->id,
                extraIds: [],
                code: $promocode->code,
            ))
            ->assertOk()
            ->assertJsonPath('data.attributes.valid', false)
            ->assertJsonPath('data.attributes.reason', 'usage_limit_reached');
    }

    public function test_validation_requires_code(): void
    {
        [$client, $service, $pricing] = $this->prepareBookingData();

        $this->actingAs($client->user)
            ->postJson(route('api.v1.promocodes.validate'), $this->payload(
                serviceId: $service->id,
                pricingId: $pricing->id,
                extraIds: [],
                code: '',
            ))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['data.attributes.code']);
    }

    private function prepareBookingData(): array
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Commercial,
        ]);
        $pricing = Pricing::factory()->for($service)->create(['amount' => 3000]);
        $extras = Extra::factory()->count(2)->create(['amount' => 100]);

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        return [$client, $service, $pricing, $extras];
    }

    private function payload(int $serviceId, int $pricingId, array $extraIds, string $code): array
    {
        return [
            'data' => [
                'attributes' => [
                    'code' => $code,
                ],
                'relationships' => [
                    'service' => [
                        'data' => ['id' => $serviceId],
                    ],
                    'pricing' => [
                        'data' => ['id' => $pricingId],
                    ],
                    'extras' => [
                        'data' => collect($extraIds)->map(fn (int $id) => ['id' => $id])->all(),
                    ],
                ],
            ],
        ];
    }
}
