<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class RegisterClientControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_client_can_register_with_username_and_password()
    {
        $avatar = File::image('avatar.jpg', 200, 200);

        $response = $this->postJson(route('api.v1.auth.register.client'), [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'username' => 'username',
                    'email' => 'john@example.com',
                    'password' => 'password',
                    'avatar' => $avatar,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $user = User::first();

        $this->assertTrue($user->isClient);
        $this->assertFalse($user->isCleaner);
        $this->assertFalse($user->isAdmin);

        $this->assertNotNull($user->avatar);
        $this->assertFileExists($user->avatarFile?->getPath() ?? '');
    }

    public function test_client_cannot_register_with_invalid_data()
    {
        $response = $this->postJson(route('api.v1.auth.register.client'), [
            'data' => [
                'attributes' => [
                    'name' => '',
                    'username' => '',
                    'email' => 'invalid-email',
                    'password' => 'short',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.name',
                'data.attributes.username',
                'data.attributes.email',
                'data.attributes.password',
            ]);
    }

    public function test_client_cannot_register_with_existing_email_and_username()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('api.v1.auth.register.client'), [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                    'username' => $user->username,
                    'email' => $user->email,
                    'password' => 'password',
                ],
            ],
        ], ['Accept-Language' => 'hu']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.email',
                'data.attributes.username'
            ]);
    }
}
