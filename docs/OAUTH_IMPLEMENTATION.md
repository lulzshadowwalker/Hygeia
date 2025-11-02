# OAuth Implementation Guide

## Overview

This document describes the OAuth social login implementation for the Hygeia platform. The system supports authentication via Google, Facebook, and Apple for both Clients and Cleaners using Laravel Socialite.

## Table of Contents

- [Architecture](#architecture)
- [Installation & Setup](#installation--setup)
- [Environment Variables](#environment-variables)
- [API Endpoints](#api-endpoints)
- [Mobile App Integration](#mobile-app-integration)
- [Database Schema](#database-schema)
- [Testing](#testing)
- [Flow Diagrams](#flow-diagrams)

## Architecture

### Components

1. **OAuthProviderInterface** (`app/Contracts/OAuthProviderInterface.php`)
   - Contract defining OAuth provider behavior
   - Ensures consistency across all OAuth implementations

2. **BaseOAuthService** (`app/Services/OAuth/BaseOAuthService.php`)
   - Abstract base class implementing common OAuth logic
   - Handles user creation, linking, and token management
   - Manages role-specific record creation (Client/Cleaner)

3. **Provider Services**
   - `GoogleOAuthService` - Google OAuth implementation
   - `FacebookOAuthService` - Facebook OAuth implementation
   - `AppleOAuthService` - Apple OAuth implementation

4. **OAuthLoginController** (`app/Http/Controllers/Api/V1/OAuthLoginController.php`)
   - Handles OAuth login/registration requests
   - Routes to appropriate OAuth service based on provider

5. **OAuthProvider Model** (`app/Models/OAuthProvider.php`)
   - Stores OAuth connection data
   - Links users to their OAuth provider accounts

## Installation & Setup

### 1. Install Required Packages

```bash
composer require laravel/socialite
composer require socialiteproviders/google
composer require socialiteproviders/facebook
composer require socialiteproviders/apple
```

### 2. Run Migrations

```bash
php artisan migrate
```

This will create the `oauth_providers` table with the following structure:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `provider` - Provider name (google, facebook, apple)
- `provider_user_id` - Unique ID from OAuth provider
- `access_token` - OAuth access token (encrypted)
- `refresh_token` - OAuth refresh token (encrypted)
- `token_expires_at` - Token expiration timestamp
- `provider_data` - JSON field for additional provider data
- Unique constraints on `[user_id, provider]` and `[provider, provider_user_id]`

### 3. Configure Service Providers

Add to `config/app.php`:

```php
'providers' => [
    // ... other providers
    \SocialiteProviders\Manager\ServiceProvider::class,
],
```

Add to `app/Providers/AppServiceProvider.php` (Laravel 11+):

```php
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;

public function boot(): void
{
    Event::listen(function (SocialiteWasCalled $event) {
        $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        $event->extendSocialite('facebook', \SocialiteProviders\Facebook\Provider::class);
        $event->extendSocialite('apple', \SocialiteProviders\Apple\Provider::class);
    });
}
```

## Environment Variables

### Required Environment Variables

Add the following to your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=your-app-url/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URI=your-app-url/auth/facebook/callback

# Apple OAuth (Option 1: With Client Secret)
APPLE_CLIENT_ID=your-apple-service-id
APPLE_CLIENT_SECRET=your-generated-client-secret
APPLE_REDIRECT_URI=your-app-url/auth/apple/callback

# Apple OAuth (Option 2: With Private Key - Recommended)
APPLE_CLIENT_ID=your-apple-service-id
APPLE_CLIENT_SECRET=
APPLE_KEY_ID=your-key-id-from-apple
APPLE_TEAM_ID=your-team-id-from-apple
APPLE_PRIVATE_KEY=/absolute/path/to/AuthKey_XYZ.p8
APPLE_PASSPHRASE=optional-passphrase-if-key-is-encrypted
APPLE_REDIRECT_URI=your-app-url/auth/apple/callback
```

### Configuration Files

Add to `config/services.php`:

```php
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
```

## API Endpoints

### POST `/api/v1/auth/oauth/login`

Handles OAuth login/registration for all providers.

#### Request Body (Client Registration)

```json
{
  "data": {
    "attributes": {
      "provider": "google",
      "oauthToken": "oauth-access-token-from-provider",
      "role": "client",
      "additionalData": {
        "avatar": "base64-image-or-file-upload"
      }
    },
    "relationships": {
      "deviceTokens": {
        "data": {
          "attributes": {
            "token": "firebase-device-token"
          }
        }
      }
    }
  }
}
```

#### Request Body (Cleaner Registration)

```json
{
  "data": {
    "attributes": {
      "provider": "facebook",
      "oauthToken": "oauth-access-token-from-provider",
      "role": "cleaner",
      "additionalData": {
        "phone": "+962792002802",
        "availableDays": ["monday", "tuesday", "wednesday"],
        "timeSlots": ["morning", "afternoon"],
        "maxHoursPerWeek": 40,
        "acceptsUrgentOffers": true,
        "yearsOfExperience": 3,
        "hasCleaningSupplies": true,
        "comfortableWithPets": true,
        "serviceRadius": 15,
        "agreedToTerms": true,
        "avatar": "base64-image-or-file-upload",
        "idCard": "base64-image-or-file-upload"
      }
    },
    "relationships": {
      "previousServices": {
        "data": [
          {"type": "service", "id": 1},
          {"type": "service", "id": 2}
        ]
      },
      "preferredServices": {
        "data": [
          {"type": "service", "id": 1},
          {"type": "service", "id": 3}
        ]
      },
      "deviceTokens": {
        "data": {
          "attributes": {
            "token": "firebase-device-token"
          }
        }
      }
    }
  }
}
```

#### Response (Success)

```json
{
  "data": {
    "type": "auth-token",
    "id": "1",
    "attributes": {
      "token": "sanctum-access-token",
      "role": "client"
    }
  }
}
```

#### Response (Error - Invalid Token)

```json
{
  "errors": [
    {
      "status": "401",
      "code": "Unauthorized",
      "title": "OAuth authentication failed",
      "detail": "Invalid OAuth token or state mismatch",
      "indicator": "OAUTH_INVALID_TOKEN"
    }
  ]
}
```

## Mobile App Integration

### Authentication Flow

#### For iOS (Google, Facebook, Apple)

1. **Initialize OAuth Provider SDK**
   - Google Sign-In SDK
   - Facebook Login SDK
   - Apple Sign In (native)

2. **User Initiates Login**
   - User taps "Sign in with Google/Facebook/Apple"

3. **Authenticate with Provider**
   ```swift
   // Example: Google Sign-In
   GIDSignIn.sharedInstance.signIn(with: config, presenting: self) { user, error in
       guard let user = user else { return }
       let idToken = user.authentication.idToken
       // Send idToken to your API
   }
   ```

4. **Send Token to Backend**
   ```swift
   let parameters = [
       "data": [
           "attributes": [
               "provider": "google",
               "oauthToken": idToken,
               "role": "client"
           ]
       ]
   ]
   
   // POST to /api/v1/auth/oauth/login
   ```

5. **Store Sanctum Token**
   - Save returned access token in secure storage (Keychain)
   - Use for subsequent API requests

#### For Android (Google, Facebook, Apple)

Similar flow using Android SDKs:
- Google Sign-In SDK for Android
- Facebook SDK for Android
- Apple Sign In for Android (if supported)

### Important Notes

1. **Stateless Authentication**: The endpoint uses `stateless()` mode, suitable for mobile apps
2. **Token Security**: Never store OAuth tokens client-side; only store the Sanctum token
3. **Role Selection**: Mobile app should ask user to select role (Client/Cleaner) before OAuth
4. **Additional Data**: For cleaners, collect additional data BEFORE OAuth or in a follow-up step

## Database Schema

### oauth_providers Table

```sql
CREATE TABLE oauth_providers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(255) NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    access_token TEXT NULL,
    refresh_token TEXT NULL,
    token_expires_at TIMESTAMP NULL,
    provider_data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_user_provider (user_id, provider),
    UNIQUE KEY unique_provider_user (provider, provider_user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Relationships

- **User** `hasMany` **OAuthProvider**
- **OAuthProvider** `belongsTo` **User**

A user can have multiple OAuth providers (e.g., linked Google AND Facebook accounts).

## Testing

### Running Tests

```bash
php artisan test --filter OAuthLoginControllerTest
```

### Test Coverage

The test suite covers:
- ✅ New user registration as Client via Google
- ✅ New user registration as Cleaner via Facebook
- ✅ Existing user login via Apple
- ✅ Linking OAuth to existing user by email
- ✅ Device token handling
- ✅ Validation of required fields
- ✅ Provider validation
- ✅ Role validation
- ✅ Unique username generation
- ✅ Email verification for OAuth users
- ✅ Multiple OAuth providers per user
- ✅ Provider data storage

### Mock Testing

Tests use Mockery to mock Socialite responses:

```php
$mockUser = Mockery::mock(SocialiteUser::class);
$mockUser->shouldReceive('getId')->andReturn('google-123');
$mockUser->shouldReceive('getEmail')->andReturn('test@example.com');

Socialite::shouldReceive('driver->stateless->userFromToken')
    ->once()
    ->andReturn($mockUser);
```

## Flow Diagrams

### New User Registration Flow

```
Mobile App → OAuth Provider → Mobile App → Backend API
    |            |               |              |
    |         Sign In         Get Token      Validate
    |            |               |           & Create
    |            ↓               ↓              User
    |        [Provider]      [API Call]         |
    |            |               |              |
    ↓            ↓               ↓              ↓
[User Taps] → [Auth] → [Return Token] → [Register/Login]
                                              ↓
                                        [Return Sanctum Token]
```

### Existing User Login Flow

```
Mobile App → OAuth Provider → Mobile App → Backend API
    |            |               |              |
    |         Sign In         Get Token      Validate
    |            |               |           & Find
    |            ↓               ↓              User
    |        [Provider]      [API Call]         |
    |            |               |              |
    ↓            ↓               ↓              ↓
[User Taps] → [Auth] → [Return Token] → [Find User]
                                              ↓
                                        [Update OAuth Token]
                                              ↓
                                        [Return Sanctum Token]
```

### Email Linking Flow

```
User exists with email@example.com (password-based)
    |
    ↓
User authenticates via Google with same email
    |
    ↓
Backend finds existing user by email
    |
    ↓
Links OAuth provider to existing user
    |
    ↓
User can now login with BOTH password AND Google
```

## Security Considerations

### 1. Token Storage
- OAuth tokens are stored encrypted in database
- Marked as `hidden` in model to prevent exposure in API responses

### 2. Password Handling
- OAuth users get random 32-character password
- Password field made nullable for OAuth-only users
- Users can later set password via "forgot password" flow

### 3. Email Verification
- OAuth users automatically verified (`email_verified_at` set)
- Assumption: OAuth providers verify emails

### 4. Unique Constraints
- User cannot link same provider twice
- Provider user ID must be unique per provider
- Prevents duplicate OAuth connections

### 5. Username Generation
- Automatically generated from OAuth nickname/email
- Ensures uniqueness with counter suffix if needed
- Follows pattern: `username`, `username_1`, `username_2`, etc.

## Troubleshooting

### Common Issues

#### 1. Invalid OAuth Token Error
**Problem**: `OAUTH_INVALID_TOKEN` error  
**Solution**: Ensure mobile app sends fresh token from provider

#### 2. Email Already Exists
**Problem**: User tries to register with OAuth but email exists  
**Solution**: System automatically links OAuth to existing account

#### 3. Username Conflicts
**Problem**: Generated username already taken  
**Solution**: System auto-increments with suffix (`_1`, `_2`, etc.)

#### 4. Missing Cleaner Data
**Problem**: Cleaner registration without required fields  
**Solution**: Ensure mobile app collects all required cleaner fields before OAuth

#### 5. Apple OAuth Configuration
**Problem**: Apple OAuth fails with invalid_client  
**Solution**: Use private key method instead of client secret, ensure correct team_id and key_id

## Future Enhancements

### Planned Features

1. **OAuth Token Refresh**
   - Automatically refresh expired OAuth tokens
   - Use refresh tokens for long-lived sessions

2. **OAuth Account Unlinking**
   - Allow users to unlink OAuth providers
   - Ensure at least one auth method remains

3. **OAuth Profile Sync**
   - Periodically sync profile data from OAuth provider
   - Update avatar, name if changed on provider

4. **Multi-Factor Authentication**
   - Combine OAuth with 2FA for enhanced security
   - SMS/TOTP verification after OAuth login

## Support

For questions or issues:
- Check test cases in `tests/Feature/Http/Controllers/Api/V1/OAuthLoginControllerTest.php`
- Review service implementations in `app/Services/OAuth/`
- Consult Laravel Socialite documentation: https://laravel.com/docs/socialite