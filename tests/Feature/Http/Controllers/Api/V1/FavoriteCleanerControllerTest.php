<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Cleaner;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteCleanerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_add_cleaner_to_favorites(): void
    {
        $client = Client::factory()->create();
        $cleaner = Cleaner::factory()->create();

        $this->actingAs($client->user);

        $this->post(route('api.v1.cleaners.favorites.store', ['cleaner' => $cleaner->id]))
            ->assertNoContent(204);

        $this->assertTrue($client->fresh()->favoriteCleaners->contains($cleaner));
    }

    public function test_client_can_remove_cleaner_from_favorites(): void
    {
        $client = Client::factory()->create();
        $cleaner = Cleaner::factory()->create();

        $client->favoriteCleaners()->attach($cleaner->id);

        $this->actingAs($client->user);

        $this->delete(route('api.v1.cleaners.favorites.destroy', ['cleaner' => $cleaner->id]))
            ->assertNoContent(204);

        $this->assertFalse($client->fresh()->favoriteCleaners->contains($cleaner));
    }
}
