<?php

namespace App\Support;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class FirebaseUserAdapter implements SocialiteUser
{
    public function __construct(
        protected array $claims,
        protected ?string $provider = null
    ) {}

    public function getId()
    {
        // If we know the provider, try to find the provider-specific ID
        // This is important for matching existing users who registered via Socialite directly
        if ($this->provider && isset($this->claims['firebase']['identities'])) {
            $domain = match ($this->provider) {
                'google' => 'google.com',
                'facebook' => 'facebook.com',
                'apple' => 'apple.com',
                default => null,
            };

            if ($domain && ! empty($this->claims['firebase']['identities'][$domain])) {
                // Return the first ID for this provider (e.g. the Google Sub ID)
                return $this->claims['firebase']['identities'][$domain][0];
            }
        }

        // Fallback to Firebase UID
        return $this->claims['sub'] ?? null;
    }

    public function getNickname()
    {
        return null;
    }

    public function getName()
    {
        return $this->claims['name'] ?? null;
    }

    public function getEmail()
    {
        return $this->claims['email'] ?? null;
    }

    public function getAvatar()
    {
        return $this->claims['picture'] ?? null;
    }

    public function getRaw()
    {
        return $this->claims;
    }

    public function setToken($token)
    {
        // Not used for Firebase user
        return $this;
    }

    public function getToken()
    {
        return null;
    }

    public function setRefreshToken($refreshToken)
    {
        return $this;
    }

    public function getRefreshToken()
    {
        return null;
    }

    public function setExpiresIn($expiresIn)
    {
        return $this;
    }

    public function getExpiresIn()
    {
        return null;
    }

    public function getApprovedScopes()
    {
        return [];
    }
}
