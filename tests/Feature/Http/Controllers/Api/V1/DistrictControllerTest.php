<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\DistrictResource;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistrictControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_all_districts(): void
    {
        District::factory()->count(3)->create();
        $resource = DistrictResource::collection(District::all());

        $response = $this->getJson(route('api.v1.districts.index'));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_shows_single_district(): void
    {
        $district = District::factory()->create();
        $resource = DistrictResource::make($district);

        $response = $this->getJson(route('api.v1.districts.show', $district));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }
}
