<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Resources\V1\CleanerResource;
use App\Http\Resources\V1\ClientResource;
use App\Models\Cleaner;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_returns_client_profile(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);
        $this->actingAs($client->user);
        $resource = ClientResource::make($client);

        $this->getJson(route('api.v1.profile.index'))
            ->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_returns_cleaner_profile(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);
        $this->actingAs($cleaner->user);
        $resource = CleanerResource::make($cleaner);

        $this->getJson(route('api.v1.profile.index'))
            ->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }
}
