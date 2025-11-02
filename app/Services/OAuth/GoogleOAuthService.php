<?php

namespace App\Services\OAuth;

class GoogleOAuthService extends BaseOAuthService
{
    /**
     * Get the provider name.
     */
    public function getProviderName(): string
    {
        return 'google';
    }
}
