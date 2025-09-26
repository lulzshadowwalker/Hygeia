<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\ExtraResource;
use App\Models\Extra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtraControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_all_extras(): void
    {
        Extra::factory()
            ->count(3)
            ->create();

        $resource = ExtraResource::collection(Extra::all());

        $response = $this->getJson(route('api.v1.extras.index'));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_shows_single_extra(): void
    {
        $extra = Extra::factory()->create();

        $resource = ExtraResource::make($extra);

        $response = $this->getJson(route('api.v1.extras.show', $extra));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }
}
