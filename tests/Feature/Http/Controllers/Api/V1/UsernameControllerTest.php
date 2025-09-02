<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_checks_existing_username(): void
    {
        $user = User::factory()->create(['username' => 'existinguser']);

        $response = $this->getJson(route('api.v1.auth.usernames.show', ['username' => 'existinguser']));

        $response->assertStatus(200);
    }

    public function test_it_checks_non_existing_username(): void
    {
        $response = $this->getJson(route('api.v1.auth.usernames.show', ['username' => 'nonexistinguser']));

        $response->assertStatus(404);
    }
}
