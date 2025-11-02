<?php

namespace App\Services\OAuth;

class FacebookOAuthService extends BaseOAuthService
{
    /**
     * Get the provider name.
     */
    public function getProviderName(): string
    {
        return 'facebook';
    }
}
