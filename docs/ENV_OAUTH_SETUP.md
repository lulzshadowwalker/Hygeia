# OAuth Environment Variables Setup Guide

## Overview

This document lists all environment variables required for OAuth social login with Google, Facebook, and Apple.

## Required Environment Variables

Add these variables to your `.env` file:

### Google OAuth

```env
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback
```

**How to obtain:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Navigate to "APIs & Services" > "Credentials"
4. Click "Create Credentials" > "OAuth client ID"
5. Select "Web application" or "iOS/Android" based on your app
6. Add authorized redirect URIs
7. Copy the Client ID and Client Secret

### Facebook OAuth

```env
FACEBOOK_CLIENT_ID=your-facebook-app-id-here
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret-here
FACEBOOK_REDIRECT_URI=https://yourdomain.com/auth/facebook/callback
```

**How to obtain:**
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app or select existing
3. Navigate to "Settings" > "Basic"
4. Copy the App ID (this is your CLIENT_ID)
5. Copy the App Secret (this is your CLIENT_SECRET)
6. Add your OAuth redirect URIs in "Facebook Login" settings

### Apple OAuth (Method 1: Using Client Secret)

```env
APPLE_CLIENT_ID=your-apple-service-id-here
APPLE_CLIENT_SECRET=your-generated-jwt-client-secret-here
APPLE_REDIRECT_URI=https://yourdomain.com/auth/apple/callback
```

**Note:** Client Secret for Apple is a JWT token with maximum 6-month lifetime. You'll need to regenerate it every 6 months.

### Apple OAuth (Method 2: Using Private Key - RECOMMENDED)

```env
APPLE_CLIENT_ID=your-apple-service-id-here
APPLE_CLIENT_SECRET=
APPLE_KEY_ID=your-key-id-from-apple-here
APPLE_TEAM_ID=your-team-id-from-apple-here
APPLE_PRIVATE_KEY=/absolute/path/to/AuthKey_XXXXXXXXXX.p8
APPLE_PASSPHRASE=
APPLE_REDIRECT_URI=https://yourdomain.com/auth/apple/callback
```

**How to obtain:**
1. Go to [Apple Developer Account](https://developer.apple.com/account/)
2. Navigate to "Certificates, Identifiers & Profiles"
3. Create an App ID
4. Create a Services ID (this is your CLIENT_ID)
5. Enable "Sign in with Apple" for your Services ID
6. Configure domains and redirect URIs
7. Create a Key for "Sign in with Apple"
8. Download the private key file (.p8)
9. Note the Key ID (10 characters)
10. Find your Team ID in the top-right corner of the developer portal

**Important:** Store the .p8 file securely and use absolute path in `APPLE_PRIVATE_KEY`.

## Configuration File Setup

After adding environment variables, ensure `config/services.php` has the following:

```php
return [
    // ... other services

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
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'key_id' => env('APPLE_KEY_ID'),
        'team_id' => env('APPLE_TEAM_ID'),
        'private_key' => env('APPLE_PRIVATE_KEY'),
        'passphrase' => env('APPLE_PASSPHRASE'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],
];
```

## Mobile App Considerations

For mobile apps, the redirect URIs work differently:

### iOS
- Use custom URL schemes or Universal Links
- Example: `myapp://auth/google/callback`
- Configure in your OAuth provider settings

### Android
- Use custom URL schemes or App Links
- Example: `myapp://auth/google/callback`
- Configure in your OAuth provider settings

### Important Notes

1. **For mobile apps**: You typically authenticate on the device and send the access token to your backend
2. **Redirect URIs**: May not be used in stateless mobile OAuth flow
3. **Token Exchange**: Mobile app gets OAuth token, sends to your API endpoint
4. **Backend Validation**: Your API validates the OAuth token with the provider

## Testing Setup

For local development, you can use:

```env
# Local Development
GOOGLE_CLIENT_ID=test-google-client-id
GOOGLE_CLIENT_SECRET=test-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

FACEBOOK_CLIENT_ID=test-facebook-client-id
FACEBOOK_CLIENT_SECRET=test-facebook-client-secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback

APPLE_CLIENT_ID=test-apple-client-id
APPLE_CLIENT_SECRET=test-apple-client-secret
APPLE_KEY_ID=test-key-id
APPLE_TEAM_ID=test-team-id
APPLE_PRIVATE_KEY=/path/to/test/key.p8
APPLE_REDIRECT_URI=http://localhost:8000/auth/apple/callback
```

**Note:** For testing, you can mock OAuth responses in your tests instead of using real credentials.

## Security Checklist

- [ ] Never commit `.env` file to version control
- [ ] Add `.env` to `.gitignore`
- [ ] Use different credentials for development, staging, and production
- [ ] Store Apple private key (.p8) outside web root
- [ ] Set proper file permissions on .p8 file (600 or 400)
- [ ] Rotate secrets regularly
- [ ] Use environment-specific redirect URIs
- [ ] Enable HTTPS for all redirect URIs in production

## Troubleshooting

### Google OAuth Issues
- **Error: redirect_uri_mismatch**
  - Ensure `GOOGLE_REDIRECT_URI` matches exactly in Google Console
  - Check for trailing slashes, http vs https

### Facebook OAuth Issues
- **Error: Can't Load URL**
  - Verify domain is added to "App Domains" in Facebook settings
  - Check "Valid OAuth Redirect URIs" in Facebook Login settings

### Apple OAuth Issues
- **Error: invalid_client**
  - Verify Team ID is correct
  - Check Key ID matches your .p8 file
  - Ensure .p8 file path is absolute and accessible
  - Verify Services ID is properly configured

## Production Deployment

Before deploying to production:

1. Generate production OAuth credentials for each provider
2. Update environment variables on your server
3. Configure proper redirect URIs (must use HTTPS)
4. Test OAuth flow in staging environment first
5. Monitor error logs for OAuth-related issues
6. Set up alerts for failed OAuth attempts

## Need Help?

- Google OAuth: https://developers.google.com/identity/protocols/oauth2
- Facebook Login: https://developers.facebook.com/docs/facebook-login
- Apple Sign In: https://developer.apple.com/sign-in-with-apple/
- Laravel Socialite: https://laravel.com/docs/socialite
- SocialiteProviders: https://socialiteproviders.com/