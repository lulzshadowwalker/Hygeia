<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username_and_password()
    {
        User::factory()->create([
            'username' => 'username',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'identifier' => 'username',
                    'password' => 'password',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'token',
                    ],
                ],
            ]);
    }

    public function test_user_can_send_device_token_with_login_request()
    {
        $user = User::factory()->create([
            'username' => 'username',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'identifier' => 'username',
                    'password' => 'password',
                ],
                'relationships' => [
                    'deviceTokens' => [
                        'data' => [
                            'attributes' => [
                                'token' => 'example-device-token',
                            ],
                        ]
                    ]
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'token',
                    ],
                ],
            ]);

        $this->assertNotNull($user->deviceTokens()->where('token', 'example-device-token')->first());
    }

    public function test_user_can_login_with_email_and_password()
    {
        User::factory()->create([
            'email' => 'email@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'identifier' => 'email@example.com',
                    'password' => 'password',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'token',
                    ],
                ],
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create();

        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'identifier' => 'foo@example.com',
                    'password' => 'foobarbaz',
                ],
            ],
        ]);

        $response->assertStatus(401);
    }
}
