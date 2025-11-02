<?php

namespace Database\Factories;

use App\Models\OAuthProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OAuthProvider>
 */
class OAuthProviderFactory extends Factory
{
    protected $model = OAuthProvider::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['google', 'facebook', 'apple']),
            'provider_user_id' => fake()->unique()->numerify('oauth-##########'),
            'access_token' => fake()->sha256(),
            'refresh_token' => fake()->boolean(50) ? fake()->sha256() : null,
            'token_expires_at' => fake()->boolean(70) ? now()->addDays(fake()->numberBetween(1, 90)) : null,
            'provider_data' => [
                'nickname' => fake()->userName(),
                'avatar' => fake()->imageUrl(),
                'email' => fake()->safeEmail(),
                'name' => fake()->name(),
            ],
        ];
    }

    /**
     * Indicate that the OAuth provider is for Google.
     */
    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'google',
            'provider_user_id' => 'google-'.fake()->unique()->numerify('##########'),
        ]);
    }

    /**
     * Indicate that the OAuth provider is for Facebook.
     */
    public function facebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'facebook',
            'provider_user_id' => 'facebook-'.fake()->unique()->numerify('##########'),
        ]);
    }

    /**
     * Indicate that the OAuth provider is for Apple.
     */
    public function apple(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'apple',
            'provider_user_id' => 'apple-'.fake()->unique()->numerify('##########'),
        ]);
    }

    /**
     * Indicate that the token is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Indicate that the token never expires.
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => null,
        ]);
    }
}
