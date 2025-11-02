<?php

namespace App\Services\OAuth;

use App\Contracts\OAuthProviderInterface;
use App\Enums\Role;
use App\Models\OAuthProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

abstract class BaseOAuthService implements OAuthProviderInterface
{
    /**
     * Handle OAuth authentication and user creation/retrieval.
     */
    public function handleOAuthCallback(
        string $oauthToken,
        Role $role,
        array $additionalData = [],
        ?string $deviceToken = null
    ): User {
        $oauthUser = $this->getUserFromToken($oauthToken);

        return $this->findOrCreateUser($oauthUser, $role, $additionalData, $deviceToken);
    }

    /**
     * Get user information from the OAuth provider.
     */
    public function getUserFromToken(string $oauthToken): SocialiteUser
    {
        return Socialite::driver($this->getProviderName())
            ->stateless()
            ->userFromToken($oauthToken);
    }

    /**
     * Find or create a user from OAuth provider data.
     */
    public function findOrCreateUser(
        SocialiteUser $oauthUser,
        Role $role,
        array $additionalData = [],
        ?string $deviceToken = null
    ): User {
        return DB::transaction(function () use ($oauthUser, $role, $additionalData, $deviceToken) {
            // Check if OAuth provider record exists
            $oauthProvider = OAuthProvider::where('provider', $this->getProviderName())
                ->where('provider_user_id', $oauthUser->getId())
                ->first();

            if ($oauthProvider) {
                // Update OAuth tokens
                $this->updateOAuthProvider($oauthProvider, $oauthUser);

                // Add device token if provided
                if ($deviceToken) {
                    $oauthProvider->user->deviceTokens()->firstOrCreate(['token' => $deviceToken]);
                }

                return $oauthProvider->user;
            }

            // Check if user exists with this email
            $user = null;
            if ($oauthUser->getEmail()) {
                $user = User::where('email', $oauthUser->getEmail())->first();
            }

            if ($user) {
                // Link OAuth provider to existing user
                $this->createOAuthProvider($user, $oauthUser);

                // Add device token if provided
                if ($deviceToken) {
                    $user->deviceTokens()->firstOrCreate(['token' => $deviceToken]);
                }

                // Assign role if not already assigned
                if (! $user->hasRole($role->value)) {
                    $user->assignRole($role->value);

                    // Create role-specific record
                    $this->createRoleSpecificRecord($user, $role, $additionalData);
                }

                return $user;
            }

            // Create new user
            $user = $this->createUser($oauthUser, $role, $additionalData, $deviceToken);

            // Link OAuth provider to new user
            $this->createOAuthProvider($user, $oauthUser);

            return $user;
        });
    }

    /**
     * Create a new user from OAuth data.
     */
    protected function createUser(
        SocialiteUser $oauthUser,
        Role $role,
        array $additionalData = [],
        ?string $deviceToken = null
    ): User {
        $user = User::create([
            'name' => $oauthUser->getName() ?? $additionalData['name'] ?? 'User',
            'email' => $oauthUser->getEmail(),
            'username' => $this->generateUsername($oauthUser),
            'password' => Hash::make(Str::random(32)), // Random password for OAuth users
            'email_verified_at' => now(), // OAuth users are considered verified
        ]);

        // Handle avatar from OAuth provider
        if ($oauthUser->getAvatar() && ! isset($additionalData['avatar'])) {
            try {
                $user->addMediaFromUrl($oauthUser->getAvatar())
                    ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            } catch (\Exception $e) {
                // Silently fail avatar download
                \Log::warning('Failed to download OAuth avatar', [
                    'provider' => $this->getProviderName(),
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif (isset($additionalData['avatar'])) {
            $user->addMedia($additionalData['avatar'])
                ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
        }

        // Add device token if provided
        if ($deviceToken) {
            $user->deviceTokens()->firstOrCreate(['token' => $deviceToken]);
        }

        // Assign role
        $user->assignRole($role->value);

        // Create role-specific record
        $this->createRoleSpecificRecord($user, $role, $additionalData);

        return $user;
    }

    /**
     * Create role-specific record (Client or Cleaner).
     */
    protected function createRoleSpecificRecord(User $user, Role $role, array $additionalData = []): void
    {
        if ($role === Role::Client) {
            $user->client()->create();
        } elseif ($role === Role::Cleaner) {
            $cleaner = $user->cleaner()->create([
                'available_days' => $additionalData['availableDays'] ?? [],
                'time_slots' => $additionalData['timeSlots'] ?? [],
                'max_hours_per_week' => $additionalData['maxHoursPerWeek'] ?? null,
                'accepts_urgent_offers' => $additionalData['acceptsUrgentOffers'] ?? false,
                'years_of_experience' => $additionalData['yearsOfExperience'] ?? 0,
                'has_cleaning_supplies' => $additionalData['hasCleaningSupplies'] ?? false,
                'comfortable_with_pets' => $additionalData['comfortableWithPets'] ?? false,
                'service_radius' => $additionalData['serviceRadius'] ?? null,
                'agreed_to_terms' => $additionalData['agreedToTerms'] ?? true,
            ]);

            // Handle ID card upload for cleaner
            if (isset($additionalData['idCard'])) {
                $cleaner->addMedia($additionalData['idCard'])
                    ->toMediaCollection($cleaner::MEDIA_COLLECTION_ID_CARD);
            }

            // Sync services
            if (isset($additionalData['previousServices'])) {
                $cleaner->previousServices()->sync($additionalData['previousServices']);
            }

            if (isset($additionalData['preferredServices'])) {
                $cleaner->preferredServices()->sync($additionalData['preferredServices']);
            }
        }
    }

    /**
     * Create OAuth provider record.
     */
    protected function createOAuthProvider(User $user, SocialiteUser $oauthUser): OAuthProvider
    {
        return $user->oauthProviders()->create([
            'provider' => $this->getProviderName(),
            'provider_user_id' => $oauthUser->getId(),
            'access_token' => $oauthUser->token ?? null,
            'refresh_token' => $oauthUser->refreshToken ?? null,
            'token_expires_at' => isset($oauthUser->expiresIn)
                ? now()->addSeconds($oauthUser->expiresIn)
                : null,
            'provider_data' => [
                'nickname' => $oauthUser->getNickname(),
                'avatar' => $oauthUser->getAvatar(),
                'email' => $oauthUser->getEmail(),
                'name' => $oauthUser->getName(),
            ],
        ]);
    }

    /**
     * Update OAuth provider record with new tokens.
     */
    protected function updateOAuthProvider(OAuthProvider $oauthProvider, SocialiteUser $oauthUser): void
    {
        $oauthProvider->update([
            'access_token' => $oauthUser->token ?? $oauthProvider->access_token,
            'refresh_token' => $oauthUser->refreshToken ?? $oauthProvider->refresh_token,
            'token_expires_at' => isset($oauthUser->expiresIn)
                ? now()->addSeconds($oauthUser->expiresIn)
                : $oauthProvider->token_expires_at,
            'provider_data' => [
                'nickname' => $oauthUser->getNickname(),
                'avatar' => $oauthUser->getAvatar(),
                'email' => $oauthUser->getEmail(),
                'name' => $oauthUser->getName(),
            ],
        ]);
    }

    /**
     * Generate a unique username from OAuth user data.
     */
    protected function generateUsername(SocialiteUser $oauthUser): string
    {
        $baseUsername = $oauthUser->getNickname()
            ?? $oauthUser->getEmail()
            ?? 'user';

        // Clean username
        $username = Str::slug(Str::before($baseUsername, '@'), '_');
        $username = Str::lower($username);

        // Ensure uniqueness
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername.'_'.$counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get the provider name (must be implemented by child classes).
     */
    abstract public function getProviderName(): string;
}
