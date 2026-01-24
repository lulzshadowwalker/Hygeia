# Firebase Authentication Integration Guide

## Overview

We have migrated our authentication system to use **Firebase Authentication**. This unifies our login process for Google, Facebook, and Apple sign-ins.
The backend API now verifies Firebase ID tokens instead of provider-specific access tokens.

## Mobile Implementation Flow

1.  **Firebase Sign-In**: The mobile app uses the Firebase SDK to sign in the user (via Google, Facebook, Apple, or Email/Password).
2.  **Get Token**: Retrieve the Firebase ID Token from the current user object (`user.getIdToken()`).
3.  **API Requests**: Send this ID Token to the Hygeia API.

## Endpoints

### 1. Check User Status
**POST** `/api/v1/auth/oauth/check`

Use this to determine if a user exists before attempting registration/login.

**Request Body:**
```json
{
  "data": {
    "attributes": {
      "provider": "google", // or 'facebook', 'apple'
      "oauthToken": "eyJhbGciOiJ..." // The Firebase ID Token
    }
  }
}
```

**Response:**
Returns `{ "data": { "attributes": { "exists": true/false, ... } } }`

### 2. Login / Register
**POST** `/api/v1/auth/oauth/login`

**Request Body (Client):**
```json
{
  "data": {
    "attributes": {
      "provider": "google",
      "oauthToken": "eyJhbGciOiJ...",
      "role": "client"
    }
  }
}
```

**Request Body (Cleaner):**
```json
{
  "data": {
    "attributes": {
      "provider": "facebook",
      "oauthToken": "eyJhbGciOiJ...",
      "role": "cleaner",
      "additionalData": {
        // ... cleaner registration fields ...
      }
    }
  }
}
```

## Error Handling

- **401 Unauthorized**: `OAUTH_INVALID_TOKEN` - The Firebase token is invalid, expired, or audience mismatch.

## key Changes for Mobile Team

- **SDK**: Ensure `firebase_auth` is fully integrated.
- **Token**: Do **not** send the raw access token from Google/Facebook SDKs. You MUST exchanges/sign-in with Firebase first and send the **Firebase ID Token**.
- **Provider Field**: Still send the `provider` field (`google`, `facebook`, `apple`) to help us categorize the login method, even though verified via Firebase.

## Testing

You can verify the flow using a generated Firebase ID Token from a test client or the Firebase Console (if available).
