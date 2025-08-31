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

    public function test_client_can_send_device_token_in_registration()
    {
        $deviceToken = 'example-device-token';
        $response = $this->postJson(route('api.v1.auth.register.client'), [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'username' => 'username',
                    'email' => 'john@example.com',
                    'password' => 'password',
                ],
                'relationships' => [
                    'deviceTokens' => [
                        'data' => [
                            'type' => 'device-tokens',
                            'attributes' => [
                                'token' => $deviceToken,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user->deviceTokens()->where('token', $deviceToken)->first());
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

    public function test_client_cannot_register_with_existing_email()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('api.v1.auth.register.client'), [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                    'username' => 'jane_doe',
                    'email' => $user->email,
                    'password' => 'password',
                ],
            ],
        ], ['Accept-Language' => 'hu']);

        $response->assertStatus(409)
            ->assertJson([
                'errors' => [
                    [
                        'status' => '409',
                        'code' => 'Conflict',
                        'title' => 'Email already exists',
                        'detail' => 'An account with this email address already exists',
                        'indicator' => 'EMAIL_ALREADY_EXISTS',
                    ],
                ],
            ]);
    }

    public function test_client_cannot_register_with_existing_username()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('api.v1.auth.register.client'), [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                    'username' => $user->username,
                    'email' => 'jane@example.com',
                    'password' => 'password',
                ],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'errors' => [
                    [
                        'status' => '409',
                        'code' => 'Conflict',
                        'title' => 'Username already exists',
                        'detail' => 'This username is already taken',
                        'indicator' => 'USERNAME_ALREADY_EXISTS',
                    ],
                ],
            ]);
    }
}
