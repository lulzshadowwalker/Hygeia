<?php

namespace App\Contracts;

use App\Enums\Role;
use App\Models\User;

interface OAuthProviderInterface
{
    /**
     * Handle OAuth authentication and user creation/retrieval.
     *
     * @param  string  $oauthToken  The OAuth access token from the provider
     * @param  Role  $role  The role to assign to the user (Client or Cleaner)
     * @param  array  $additionalData  Additional data required for registration (e.g., cleaner-specific fields)
     * @param  string|null  $deviceToken  Optional device token for push notifications
     * @return User The authenticated or newly created user
     *
     * @throws \Exception If OAuth authentication fails
     */
    public function handleOAuthCallback(
        string $oauthToken,
        Role $role,
        array $additionalData = [],
        ?string $deviceToken = null
    ): User;

    /**
     * Get user information from the OAuth provider.
     *
     * @param  string  $oauthToken  The OAuth access token from the provider
     *
     * @throws \Exception If fetching user info fails
     */
    public function getUserFromToken(string $oauthToken): \Laravel\Socialite\Contracts\User;

    /**
     * Get the provider name (google, facebook, apple).
     */
    public function getProviderName(): string;

    /**
     * Find or create a user from OAuth provider data.
     */
    public function findOrCreateUser(
        \Laravel\Socialite\Contracts\User $oauthUser,
        Role $role,
        array $additionalData = [],
        ?string $deviceToken = null
    ): User;
}
