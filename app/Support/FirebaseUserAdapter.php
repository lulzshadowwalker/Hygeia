<?php

namespace App\Support;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class FirebaseUserAdapter implements SocialiteUser
{
    public function __construct(protected array $claims) {}

    public function getId()
    {
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
