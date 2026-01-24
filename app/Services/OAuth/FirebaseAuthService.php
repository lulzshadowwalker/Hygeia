<?php

namespace App\Services\OAuth;

use App\Support\FirebaseUserAdapter;
use Google\Client;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class FirebaseAuthService extends BaseOAuthService
{
    protected string $providerName;

    public function setProviderName(string $name): self
    {
        $this->providerName = $name;

        return $this;
    }

    public function getProviderName(): string
    {
        if (! isset($this->providerName)) {
            throw new \RuntimeException('Provider name must be set before using FirebaseAuthService.');
        }

        return $this->providerName;
    }

    public function getUserFromToken(string $token): SocialiteUser
    {
        $client = new Client;
        // The service file contains credentials to initialize the client,
        // but for verifying ID tokens we just need to verify the signature against Google's public keys.
        // However, passing the service file doesn't hurt and might be needed for other interactions.
        $client->setAuthConfig(config('services.firebase.service_file'));

        try {
            $payload = $client->verifyIdToken($token);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid Firebase ID Token: '.$e->getMessage());
        }

        if (! $payload) {
            throw new \InvalidArgumentException('Invalid Firebase ID Token');
        }

        // Verify audience matches Project ID
        $projectId = config('services.firebase.project_id');
        if (($payload['aud'] ?? '') !== $projectId) {
            throw new \InvalidArgumentException('Token audience does not match Project ID');
        }

        return new FirebaseUserAdapter($payload);
    }
}
