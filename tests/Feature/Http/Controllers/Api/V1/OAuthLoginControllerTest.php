<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Models\OAuthProvider;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class OAuthLoginControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function mockSocialiteUser(array $data = []): SocialiteUser
    {
        $user = Mockery::mock(SocialiteUser::class);
        $user->shouldReceive('getId')->andReturn($data['id'] ?? '123456789');
        $user
            ->shouldReceive('getEmail')
            ->andReturn($data['email'] ?? 'test@example.com');
        $user->shouldReceive('getName')->andReturn($data['name'] ?? 'John Doe');
        $user
            ->shouldReceive('getNickname')
            ->andReturn($data['nickname'] ?? 'johndoe');
        $user
            ->shouldReceive('getAvatar')
            ->andReturn($data['avatar'] ?? 'https://example.com/avatar.jpg');
        $user->token = $data['token'] ?? 'mock-oauth-token';
        $user->refreshToken = $data['refreshToken'] ?? null;
        $user->expiresIn = $data['expiresIn'] ?? 3600;

        return $user;
    }

    public function test_new_user_can_register_as_client_with_google_oauth()
    {
        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-123',
            'email' => 'client@example.com',
            'name' => 'Client User',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-oauth-access-token',
                    'role' => 'client',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => ['type', 'id', 'attributes' => ['token']],
        ]);

        $user = User::where('email', 'client@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isClient);
        $this->assertFalse($user->isCleaner);
        $this->assertNotNull($user->client);

        $oauthProvider = OAuthProvider::where('user_id', $user->id)
            ->where('provider', 'google')
            ->first();
        $this->assertNotNull($oauthProvider);
        $this->assertEquals('google-123', $oauthProvider->provider_user_id);
    }

    public function test_new_user_can_register_as_cleaner_with_facebook_oauth()
    {
        Service::factory()->count(4)->create();

        $mockUser = $this->mockSocialiteUser([
            'id' => 'facebook-456',
            'email' => 'cleaner@example.com',
            'name' => 'Cleaner User',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'facebook',
                    'oauthToken' => 'mock-facebook-token',
                    'role' => 'cleaner',
                    'additionalData' => [
                        'phone' => '+962792002802',
                        'availableDays' => ['monday', 'tuesday', 'wednesday'],
                        'timeSlots' => ['morning', 'afternoon'],
                        'maxHoursPerWeek' => 40,
                        'acceptsUrgentOffers' => true,
                        'yearsOfExperience' => 3,
                        'hasCleaningSupplies' => true,
                        'comfortableWithPets' => true,
                        'serviceRadius' => 15,
                        'agreedToTerms' => true,
                    ],
                ],
                'relationships' => [
                    'previousServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 2],
                        ],
                    ],
                    'preferredServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 3],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => ['type', 'id', 'attributes' => ['token']],
        ]);

        $user = User::where('email', 'cleaner@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isCleaner);
        $this->assertFalse($user->isClient);
        $this->assertNotNull($user->cleaner);
        $this->assertEquals(40, $user->cleaner->max_hours_per_week);
        $this->assertCount(2, $user->cleaner->previousServices);
        $this->assertCount(2, $user->cleaner->preferredServices);

        $oauthProvider = OAuthProvider::where('user_id', $user->id)
            ->where('provider', 'facebook')
            ->first();
        $this->assertNotNull($oauthProvider);
    }

    public function test_existing_user_can_login_with_apple_oauth()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole(Role::Client->value);
        $user->client()->create();

        OAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'apple',
            'provider_user_id' => 'apple-789',
            'access_token' => 'old-token',
        ]);

        $mockUser = $this->mockSocialiteUser([
            'id' => 'apple-789',
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'token' => 'new-apple-token',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'apple',
                    'oauthToken' => 'new-apple-token',
                    'role' => 'client',
                ],
            ],
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $oauthProvider = $user
            ->oauthProviders()
            ->where('provider', 'apple')
            ->first();
        $this->assertEquals('new-apple-token', $oauthProvider->access_token);
    }

    public function test_oauth_login_links_to_existing_user_by_email()
    {
        $existingUser = User::factory()->create([
            'email' => 'linkemail@example.com',
            'password' => Hash::make('password'),
        ]);
        $existingUser->assignRole(Role::Client->value);
        $existingUser->client()->create();

        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-new-id',
            'email' => 'linkemail@example.com',
            'name' => 'Link User',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-google-token',
                    'role' => 'client',
                ],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertEquals(
            1,
            User::where('email', 'linkemail@example.com')->count(),
        );

        $oauthProvider = OAuthProvider::where('user_id', $existingUser->id)
            ->where('provider', 'google')
            ->first();
        $this->assertNotNull($oauthProvider);
        $this->assertEquals('google-new-id', $oauthProvider->provider_user_id);
    }

    public function test_oauth_login_with_device_token()
    {
        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-device',
            'email' => 'device@example.com',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $deviceToken = 'test-device-token-123';

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-oauth-token',
                    'role' => 'client',
                ],
                'relationships' => [
                    'deviceTokens' => [
                        'data' => [
                            'attributes' => [
                                'token' => $deviceToken,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $user = User::where('email', 'device@example.com')->first();
        $this->assertNotNull(
            $user->deviceTokens()->where('token', $deviceToken)->first(),
        );
    }

    public function test_oauth_login_validates_required_fields()
    {
        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.provider',
                'data.attributes.oauthToken',
                'data.attributes.role',
            ]);
    }

    public function test_oauth_login_validates_provider_value()
    {
        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'invalid-provider',
                    'oauthToken' => 'token',
                    'role' => 'client',
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.provider']);
    }

    public function test_oauth_login_validates_role_value()
    {
        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'token',
                    'role' => 'invalid-role',
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.role']);
    }

    public function test_oauth_login_generates_unique_username()
    {
        // Create existing user with username
        User::factory()->create(['username' => 'johndoe']);

        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-username-test',
            'email' => 'newjohn@example.com',
            'nickname' => 'johndoe', // Same nickname as existing user
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-oauth-token',
                    'role' => 'client',
                ],
            ],
        ]);

        $response->assertStatus(200);

        $user = User::where('email', 'newjohn@example.com')->first();
        $this->assertNotEquals('johndoe', $user->username);
        $this->assertStringStartsWith('johndoe_', $user->username);
    }

    public function test_oauth_login_sets_email_verified_at_for_new_users()
    {
        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-verified',
            'email' => 'verified@example.com',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-oauth-token',
                    'role' => 'client',
                ],
            ],
        ]);

        $response->assertStatus(200);

        $user = User::where('email', 'verified@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_multiple_oauth_providers_can_link_to_same_user()
    {
        $user = User::factory()->create(['email' => 'multi@example.com']);
        $user->assignRole(Role::Client->value);
        $user->client()->create();

        // Link Google
        OAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-multi',
        ]);

        // Now link Facebook
        $mockUser = $this->mockSocialiteUser([
            'id' => 'facebook-multi',
            'email' => 'multi@example.com',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'facebook',
                    'oauthToken' => 'mock-facebook-token',
                    'role' => 'client',
                ],
            ],
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertCount(2, $user->oauthProviders);
        $this->assertNotNull(
            $user->oauthProviders()->where('provider', 'google')->first(),
        );
        $this->assertNotNull(
            $user->oauthProviders()->where('provider', 'facebook')->first(),
        );
    }

    public function test_oauth_login_stores_provider_data()
    {
        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-data-test',
            'email' => 'providerdata@example.com',
            'name' => 'Provider Test',
            'nickname' => 'providertest',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.login'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-oauth-token',
                    'role' => 'client',
                ],
            ],
        ]);

        $response->assertStatus(200);

        $user = User::where('email', 'providerdata@example.com')->first();
        $oauthProvider = $user
            ->oauthProviders()
            ->where('provider', 'google')
            ->first();

        $this->assertEquals(
            'Provider Test',
            $oauthProvider->provider_data['name'],
        );
        $this->assertEquals(
            'providertest',
            $oauthProvider->provider_data['nickname'],
        );
        $this->assertEquals(
            'https://example.com/avatar.jpg',
            $oauthProvider->provider_data['avatar'],
        );
    }

    public function test_oauth_check_returns_true_for_existing_user_with_oauth_link()
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $user->assignRole(Role::Client->value);
        $user->client()->create();

        OAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-check-123',
        ]);

        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-check-123',
            'email' => 'existing@example.com',
            'name' => 'Existing User',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.check'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-oauth-token',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'type' => 'oauth-check',
                'attributes' => [
                    'exists' => true,
                    'provider' => 'google',
                    'email' => 'existing@example.com',
                    'role' => 'client',
                    'userId' => $user->id,
                ],
            ],
        ]);
    }

    public function test_oauth_check_returns_true_for_existing_user_with_matching_email()
    {
        $user = User::factory()->create(['email' => 'emailmatch@example.com']);
        $user->assignRole(Role::Cleaner->value);
        $user->cleaner()->create([
            'available_days' => ['monday', 'tuesday'],
            'time_slots' => ['morning'],
            'max_hours_per_week' => 40,
            'accepts_urgent_offers' => true,
            'years_of_experience' => 2,
            'has_cleaning_supplies' => true,
            'comfortable_with_pets' => true,
            'service_radius' => 10,
            'agreed_to_terms' => true,
        ]);

        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-new-provider',
            'email' => 'emailmatch@example.com',
            'name' => 'Email Match User',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.check'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-oauth-token',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'type' => 'oauth-check',
                'attributes' => [
                    'exists' => true,
                    'provider' => 'google',
                    'email' => 'emailmatch@example.com',
                    'role' => 'cleaner',
                    'userId' => $user->id,
                ],
            ],
        ]);
    }

    public function test_oauth_check_returns_false_for_new_user()
    {
        $mockUser = $this->mockSocialiteUser([
            'id' => 'google-new-user',
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'nickname' => 'newuser',
        ]);

        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andReturn($mockUser);

        $response = $this->postJson(route('api.v1.auth.oauth.check'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'mock-oauth-token',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'type' => 'oauth-check',
                'attributes' => [
                    'exists' => false,
                    'provider' => 'google',
                    'email' => 'newuser@example.com',
                    'name' => 'New User',
                ],
            ],
        ]);

        // Ensure role and userId are null for new users
        $this->assertNull($response->json('data.attributes.role'));
        $this->assertNull($response->json('data.attributes.userId'));
    }

    public function test_oauth_check_validates_required_fields()
    {
        $response = $this->postJson(route('api.v1.auth.oauth.check'), [
            'data' => [
                'attributes' => [],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.provider',
                'data.attributes.oauthToken',
            ]);
    }

    public function test_oauth_check_validates_provider_value()
    {
        $response = $this->postJson(route('api.v1.auth.oauth.check'), [
            'data' => [
                'attributes' => [
                    'provider' => 'invalid-provider',
                    'oauthToken' => 'token',
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.provider']);
    }

    public function test_oauth_check_handles_invalid_token()
    {
        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->once()
            ->andThrow(new \Laravel\Socialite\Two\InvalidStateException);

        $response = $this->postJson(route('api.v1.auth.oauth.check'), [
            'data' => [
                'attributes' => [
                    'provider' => 'google',
                    'oauthToken' => 'invalid-token',
                ],
            ],
        ]);

        $response->assertStatus(401)->assertJson([
            'errors' => [
                [
                    'status' => '401',
                    'indicator' => 'OAUTH_INVALID_TOKEN',
                ],
            ],
        ]);
    }
}
