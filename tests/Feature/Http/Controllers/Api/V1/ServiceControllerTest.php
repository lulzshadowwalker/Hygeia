<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\ServiceType;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Pricing;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_all_services(): void
    {
        Service::factory()
            ->has(Pricing::factory()->count(2), 'pricings')
            ->count(3)
            ->create();

        $resource = ServiceResource::collection(Service::with('pricings')->get());

        $response = $this->getJson(route('api.v1.services.index'));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_filters_services_by_type(): void
    {
        $service1 = Service::factory()->create(['type' => ServiceType::Residential]);
        $service2 = Service::factory()->create(['type' => ServiceType::Commercial]);

        Service::factory()
            ->has(Pricing::factory()->count(2), 'pricings')
            ->count(2)
            ->create();

        $resource = ServiceResource::collection(Service::with('pricings')->where('type', ServiceType::Residential)->get());
        $response = $this->getJson(route('api.v1.services.index', ['type' => ServiceType::Residential->value]));

        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));

        $this->assertCount(1, $response->json());
        $this->assertEquals($service1->id, $response->json()['data'][0]['id']);
    }

    public function test_it_shows_single_service(): void
    {
        $service = Service::factory()
            ->has(Pricing::factory()->count(2), 'pricings')
            ->create();

        $resource = ServiceResource::make($service->load('pricings'));

        $response = $this->getJson(route('api.v1.services.show', $service));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }
}
