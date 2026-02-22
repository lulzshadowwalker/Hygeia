<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Resources\V1\CleanerResource;
use App\Models\Cleaner;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class FavoriteCleanerControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_client_can_list_favorite_cleaners(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client->value);
        $favoriteCleaners = Cleaner::factory()->count(2)->create();
        Cleaner::factory()->create();

        $client->favoriteCleaners()->sync($favoriteCleaners->pluck('id'));

        $this->actingAs($client->user);

        $resource = CleanerResource::collection($favoriteCleaners);

        $this->getJson(route('api.v1.cleaners.favorites.index'))
            ->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_favorite_cleaners_index_is_auth_protected(): void
    {
        $this->getJson(route('api.v1.cleaners.favorites.index'))
            ->assertUnauthorized();
    }

    public function test_only_clients_can_list_favorite_cleaners(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner->value);

        $this->actingAs($cleaner->user);

        $this->getJson(route('api.v1.cleaners.favorites.index'))
            ->assertForbidden();
    }

    public function test_client_can_add_cleaner_to_favorites(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client->value);
        $cleaner = Cleaner::factory()->create();

        $this->actingAs($client->user);

        $this->post(route('api.v1.cleaners.favorites.store', ['cleaner' => $cleaner->id]))
            ->assertNoContent(204);

        $this->assertTrue($client->fresh()->favoriteCleaners->contains($cleaner));
    }

    public function test_client_can_remove_cleaner_from_favorites(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client->value);
        $cleaner = Cleaner::factory()->create();

        $client->favoriteCleaners()->attach($cleaner->id);

        $this->actingAs($client->user);

        $this->delete(route('api.v1.cleaners.favorites.destroy', ['cleaner' => $cleaner->id]))
            ->assertNoContent(204);

        $this->assertFalse($client->fresh()->favoriteCleaners->contains($cleaner));
    }
}
