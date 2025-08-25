<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallbackRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_callback_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('api.v1.callback-requests.store'));

        $response->assertNoContent(201);
        $this->assertDatabaseHas('callback_requests', [
            'user_id' => $user->id,
        ]);
    }

    public function test_it_does_not_create_duplicate_callback_request_within_10_minutes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response1 = $this->postJson(route('api.v1.callback-requests.store'));
        $response1->assertNoContent(201);

        $response2 = $this->postJson(route('api.v1.callback-requests.store'));
        $response2->assertNoContent(200);

        $this->assertDatabaseCount('callback_requests', 1);
    }

    public function test_it_duplicates_callback_request_after_10_minutes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response1 = $this->postJson(route('api.v1.callback-requests.store'));
        $response1->assertNoContent(201);

        // Fast-forward time by 11 minutes
        $this->travel(11)->minutes();

        $response2 = $this->postJson(route('api.v1.callback-requests.store'));
        $response2->assertNoContent(201);

        $this->assertDatabaseCount('callback_requests', 2);
    }
}
