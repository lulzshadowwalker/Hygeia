<?php

namespace App\Services\OAuth;

class AppleOAuthService extends BaseOAuthService
{
    /**
     * Get the provider name.
     */
    public function getProviderName(): string
    {
        return 'apple';
    }
}
