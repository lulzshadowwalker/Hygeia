<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_changes_password_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('currentPassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.auth.change-password'), [
                'data' => [
                    'attributes' => [
                        'current_password' => 'currentPassword123',
                        'new_password' => 'newSecurePassword123',
                        'new_password_confirmation' => 'newSecurePassword123',
                    ],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'password-change',
                    'attributes' => [
                        'message' => 'Password has been changed successfully.',
                    ],
                ],
            ]);

        // Verify password was actually changed
        $user->refresh();
        $this->assertTrue(Hash::check('newSecurePassword123', $user->password));
        $this->assertFalse(Hash::check('currentPassword123', $user->password));
    }

    public function test_it_fails_with_incorrect_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('currentPassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.auth.change-password'), [
                'data' => [
                    'attributes' => [
                        'current_password' => 'wrongPassword',
                        'new_password' => 'newSecurePassword123',
                        'new_password_confirmation' => 'newSecurePassword123',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'status' => '422',
                        'title' => 'Validation Error',
                        'detail' => 'The current password is incorrect.',
                        'source' => [
                            'pointer' => '/data/attributes/current_password',
                        ],
                    ],
                ],
            ]);

        // Verify password was not changed
        $user->refresh();
        $this->assertTrue(Hash::check('currentPassword123', $user->password));
    }

    public function test_it_requires_authentication()
    {
        $response = $this->postJson(route('api.v1.auth.change-password'), [
            'data' => [
                'attributes' => [
                    'current_password' => 'currentPassword123',
                    'new_password' => 'newSecurePassword123',
                    'new_password_confirmation' => 'newSecurePassword123',
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_it_validates_required_fields()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.auth.change-password'), [
                'data' => [
                    'attributes' => [],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.current_password',
                'data.attributes.new_password',
            ]);
    }

    public function test_it_validates_password_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('currentPassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.auth.change-password'), [
                'data' => [
                    'attributes' => [
                        'current_password' => 'currentPassword123',
                        'new_password' => 'newSecurePassword123',
                        'new_password_confirmation' => 'differentPassword123',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.new_password']);
    }

    public function test_it_validates_minimum_password_length()
    {
        $user = User::factory()->create([
            'password' => Hash::make('currentPassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.auth.change-password'), [
                'data' => [
                    'attributes' => [
                        'current_password' => 'currentPassword123',
                        'new_password' => '123',
                        'new_password_confirmation' => '123',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.new_password']);
    }

    public function test_it_requires_new_password_to_be_different_from_current()
    {
        $user = User::factory()->create([
            'password' => Hash::make('samePassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.auth.change-password'), [
                'data' => [
                    'attributes' => [
                        'current_password' => 'samePassword123',
                        'new_password' => 'samePassword123',
                        'new_password_confirmation' => 'samePassword123',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.new_password']);
    }

    public function test_user_can_login_with_new_password_after_change()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldPassword123'),
        ]);

        // Change password
        $this->actingAs($user)
            ->postJson(route('api.v1.auth.change-password'), [
                'data' => [
                    'attributes' => [
                        'current_password' => 'oldPassword123',
                        'new_password' => 'newPassword123',
                        'new_password_confirmation' => 'newPassword123',
                    ],
                ],
            ])
            ->assertStatus(200);

        // Try to login with new password
        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'identifier' => 'test@example.com',
                    'password' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'attributes' => [
                        'token',
                        'role',
                    ],
                ],
            ]);

        // Verify old password no longer works
        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'identifier' => 'test@example.com',
                    'password' => 'oldPassword123',
                ],
            ],
        ]);

        $response->assertStatus(401);
    }
}
