<?php

namespace App\Services\OAuth;

use App\Support\FirebaseUserAdapter;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
        $projectId = config('services.firebase.project_id');
        $keys = $this->getPublicKeys();

        try {
            $decoded = JWT::decode($token, $keys);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid Firebase ID Token: '.$e->getMessage());
        }

        // Convert stdClass to array recursively
        $payload = json_decode(json_encode($decoded), true);

        // Verify audience matches Project ID
        if (($payload['aud'] ?? '') !== $projectId) {
            throw new \InvalidArgumentException('Token audience does not match Project ID');
        }

        // Verify issuer
        if (($payload['iss'] ?? '') !== "https://securetoken.google.com/{$projectId}") {
            throw new \InvalidArgumentException('Token issuer does not match Project ID');
        }

        return new FirebaseUserAdapter($payload, $this->providerName ?? null);
    }

    protected function getPublicKeys(): array
    {
        $keys = Cache::remember('firebase_public_keys', 3600, function () {
            $response = Http::get('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');

            if ($response->failed()) {
                throw new \RuntimeException('Failed to fetch Firebase public keys');
            }

            return $response->json();
        });

        return array_map(fn ($key) => new Key($key, 'RS256'), $keys);
    }
}
