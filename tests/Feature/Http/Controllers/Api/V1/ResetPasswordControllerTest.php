<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resets_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldPassword123'),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => $token,
                    'email' => 'test@example.com',
                    'password' => 'newPassword123',
                    'password_confirmation' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'password-reset',
                    'attributes' => [
                        'message' => 'Password has been reset successfully.',
                    ],
                ],
            ]);

        // Verify password was actually changed
        $user->refresh();
        $this->assertTrue(Hash::check('newPassword123', $user->password));
        $this->assertFalse(Hash::check('oldPassword123', $user->password));
    }

    public function test_it_fails_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => 'invalid-token',
                    'email' => 'test@example.com',
                    'password' => 'newPassword123',
                    'password_confirmation' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_it_fails_with_nonexistent_email()
    {
        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => 'some-token',
                    'email' => 'nonexistent@example.com',
                    'password' => 'newPassword123',
                    'password_confirmation' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_it_validates_required_fields()
    {
        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.token',
                'data.attributes.email',
                'data.attributes.password',
            ]);
    }

    public function test_it_validates_password_confirmation()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => $token,
                    'email' => 'test@example.com',
                    'password' => 'newPassword123',
                    'password_confirmation' => 'differentPassword123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.password']);
    }

    public function test_it_validates_minimum_password_length()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => $token,
                    'email' => 'test@example.com',
                    'password' => '123',
                    'password_confirmation' => '123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.password']);
    }

    public function test_it_validates_email_format()
    {
        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => 'some-token',
                    'email' => 'invalid-email-format',
                    'password' => 'newPassword123',
                    'password_confirmation' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_it_fails_with_expired_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Create a token and then manually expire it by setting the created time to past expiry
        $token = Password::createToken($user);

        // Simulate expired token by updating the database directly
        DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->update(['created_at' => now()->subHours(2)]); // Assuming 60 minute expiry

        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => $token,
                    'email' => 'test@example.com',
                    'password' => 'newPassword123',
                    'password_confirmation' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_token_is_deleted_after_successful_reset()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Password::createToken($user);

        // Verify token exists
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => $token,
                    'email' => 'test@example.com',
                    'password' => 'newPassword123',
                    'password_confirmation' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Verify token is deleted after successful reset
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'test@example.com',
        ]);
    }
}
