<?php

namespace App\Traits;

use Google\Client;
use GuzzleHttp\Psr7\Uri;

trait InteractsWithFirebase
{
    /*
     * Get the base URL for Firebase services
     */
    protected static function baseUrl(): Uri
    {
        $base = config('services.firebase.base_url').'/'.config('services.firebase.project_id');
        $base = preg_replace('/([^:])(\/{2,})/', '$1/', $base);

        return new Uri($base);
    }

    /**
     * Get the full Firebase url for the given endpoint.
     */
    protected static function endpoint(string $endpoint): Uri
    {
        $full = self::baseUrl().'/'.$endpoint;
        $full = preg_replace('/([^:])(\/{2,})/', '$1/', $full);

        return new Uri($full);
    }

    /**
     * Get the Firebase access token.
     * Scopes are set to messaging only.
     */
    protected static function accessToken(): string
    {
        $client = new Client;
        $client->setAuthConfig(config('services.firebase.service_file'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();

        return $client->getAccessToken()['access_token'];
    }
}
