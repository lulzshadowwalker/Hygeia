<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'firebase' => [
        'base_url' => env('FIREBASE_BASE_URL', 'https://fcm.googleapis.com/v1/projects/'),
        'service_file' => storage_path('../' . env('FIREBASE_SERVICE_FILE', '../firebase/dev.json')),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'messaging' => [],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'), // Required. Bundle ID from Identifier in Apple Developer.
        'client_secret' => env('APPLE_CLIENT_SECRET'), // Empty. We create it from private key.
        'key_id' => env('APPLE_KEY_ID'), // Required. Key ID from Keys in Apple Developer.
        'team_id' => env('APPLE_TEAM_ID'), // Required. App ID Prefix from Identifier in Apple Developer.
        'private_key' => env('APPLE_PRIVATE_KEY'), // Required. Must be absolute path, e.g. /var/www/cert/AuthKey_XYZ.p8
        'passphrase' => env('APPLE_PASSPHRASE'), // Optional. Set if your private key have a passphrase.
        'redirect' => env('APPLE_REDIRECT_URI') // Required.
    ],

];
