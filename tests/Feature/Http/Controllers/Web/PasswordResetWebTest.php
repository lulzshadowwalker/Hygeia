<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_form_displays_correctly()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token]).'?email=test@example.com');

        $response->assertStatus(200)
            ->assertSee('Reset Password')
            ->assertSee('test@example.com')
            ->assertSee($token);
    }

    public function test_password_reset_form_submission_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('oldPassword123'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newPassword123',
            'password_confirmation' => 'newPassword123',
        ]);

        $response->assertStatus(200)
            ->assertSee('Password Reset Successful!');

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newPassword123', $user->password));
    }

    public function test_password_reset_form_shows_validation_errors()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newPassword123',
            'password_confirmation' => 'differentPassword123',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password']);
    }

    public function test_password_reset_form_handles_invalid_token()
    {
        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'newPassword123',
            'password_confirmation' => 'newPassword123',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }
}
