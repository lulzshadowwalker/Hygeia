# Mobile OAuth Quick Start Guide

## For Mobile Developers

This is a quick reference for integrating OAuth social login in the Hygeia mobile app.

## Overview

The Hygeia API supports OAuth login via:
- ✅ Google
- ✅ Facebook  
- ✅ Apple

Both **Clients** and **Cleaners** can register/login using OAuth.

## API Endpoint

```
POST /api/v1/auth/oauth/login
```

## Authentication Flow

```
1. Mobile App → Authenticate with OAuth Provider (Google/Facebook/Apple)
2. Mobile App → Receive OAuth Access Token
3. Mobile App → Send Token to Hygeia API
4. Hygeia API → Validate Token & Create/Login User
5. Hygeia API → Return Sanctum Access Token
6. Mobile App → Store Sanctum Token for Future API Calls
```

## Implementation Steps

### Step 1: Integrate OAuth Provider SDK

#### iOS

```swift
// Google Sign-In
import GoogleSignIn

// Facebook Login
import FBSDKLoginKit

// Apple Sign In (Native)
import AuthenticationServices
```

#### Android

```kotlin
// Google Sign-In
implementation 'com.google.android.gms:play-services-auth:20.7.0'

// Facebook Login
implementation 'com.facebook.android:facebook-login:16.1.3'

// Apple Sign In
// Use third-party library or server-side flow
```

### Step 2: Authenticate User with Provider

#### iOS - Google Example

```swift
let config = GIDConfiguration(clientID: "YOUR_GOOGLE_CLIENT_ID")

GIDSignIn.sharedInstance.signIn(with: config, presenting: self) { user, error in
    guard let user = user else { return }
    
    // Get the ID token
    let idToken = user.authentication.idToken
    
    // Send to your backend
    self.sendOAuthTokenToBackend(
        provider: "google",
        token: idToken,
        role: "client"
    )
}
```

#### iOS - Facebook Example

```swift
let loginManager = LoginManager()

loginManager.logIn(permissions: ["public_profile", "email"], from: self) { result, error in
    guard let token = AccessToken.current?.tokenString else { return }
    
    // Send to your backend
    self.sendOAuthTokenToBackend(
        provider: "facebook",
        token: token,
        role: "client"
    )
}
```

#### iOS - Apple Example

```swift
let request = ASAuthorizationAppleIDProvider().createRequest()
request.requestedScopes = [.fullName, .email]

let controller = ASAuthorizationController(authorizationRequests: [request])
controller.delegate = self
controller.presentationContextProvider = self
controller.performRequests()

// In delegate method:
func authorizationController(controller: ASAuthorizationController, 
                            didCompleteWithAuthorization authorization: ASAuthorization) {
    if let credential = authorization.credential as? ASAuthorizationAppleIDCredential {
        let identityToken = String(data: credential.identityToken!, encoding: .utf8)
        
        // Send to your backend
        self.sendOAuthTokenToBackend(
            provider: "apple",
            token: identityToken,
            role: "client"
        )
    }
}
```

### Step 3: Send Token to Hygeia API

#### Client Registration

```swift
func sendOAuthTokenToBackend(provider: String, token: String, role: String) {
    let url = URL(string: "https://your-api.com/api/v1/auth/oauth/login")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("application/json", forHTTPHeaderField: "Content-Type")
    
    let body: [String: Any] = [
        "data": [
            "attributes": [
                "provider": provider,
                "oauthToken": token,
                "role": role
            ],
            "relationships": [
                "deviceTokens": [
                    "data": [
                        "attributes": [
                            "token": "your-firebase-device-token"
                        ]
                    ]
                ]
            ]
        ]
    ]
    
    request.httpBody = try? JSONSerialization.data(withJSONObject: body)
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        // Handle response
        if let data = data,
           let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
           let dataNode = json["data"] as? [String: Any],
           let attributes = dataNode["attributes"] as? [String: Any],
           let accessToken = attributes["token"] as? String {
            
            // Store the Sanctum token securely
            self.storeAccessToken(accessToken)
        }
    }.resume()
}
```

#### Cleaner Registration

For cleaners, you need to collect additional data first:

```swift
let body: [String: Any] = [
    "data": [
        "attributes": [
            "provider": "google",
            "oauthToken": token,
            "role": "cleaner",
            "additionalData": [
                "phone": "+962792002802",
                "availableDays": ["monday", "tuesday", "wednesday"],
                "timeSlots": ["morning", "afternoon"],
                "maxHoursPerWeek": 40,
                "acceptsUrgentOffers": true,
                "yearsOfExperience": 3,
                "hasCleaningSupplies": true,
                "comfortableWithPets": true,
                "serviceRadius": 15,
                "agreedToTerms": true
            ]
        ],
        "relationships": [
            "previousServices": [
                "data": [
                    ["type": "service", "id": 1],
                    ["type": "service", "id": 2]
                ]
            ],
            "preferredServices": [
                "data": [
                    ["type": "service", "id": 1],
                    ["type": "service", "id": 3]
                ]
            ],
            "deviceTokens": [
                "data": [
                    "attributes": [
                        "token": "firebase-device-token"
                    ]
                ]
            ]
        ]
    ]
]
```

### Step 4: Handle Response

#### Success Response

```json
{
  "data": {
    "type": "auth-token",
    "id": "1",
    "attributes": {
      "token": "1|abcdef123456...",
      "role": "client"
    }
  }
}
```

#### Error Response

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

### Step 5: Store & Use Sanctum Token

```swift
// Store token securely (Keychain recommended)
func storeAccessToken(_ token: String) {
    // Store in Keychain
    KeychainHelper.save(token, forKey: "access_token")
}

// Use token for API requests
func makeAuthenticatedRequest() {
    let token = KeychainHelper.load(forKey: "access_token")
    
    var request = URLRequest(url: apiURL)
    request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
    request.setValue("application/json", forHTTPHeaderField: "Accept")
    
    // Make request...
}
```

## Request Parameters

### Required Fields (All Roles)

| Field | Type | Description |
|-------|------|-------------|
| `provider` | string | OAuth provider: `google`, `facebook`, or `apple` |
| `oauthToken` | string | Access token from OAuth provider |
| `role` | string | User role: `client` or `cleaner` |

### Optional Fields (All Roles)

| Field | Type | Description |
|-------|------|-------------|
| `deviceTokens.data.attributes.token` | string | Firebase device token for push notifications |

### Required Additional Data (Cleaners Only)

| Field | Type | Description |
|-------|------|-------------|
| `phone` | string | Phone number |
| `availableDays` | array | Days available: `["monday", "tuesday", ...]` |
| `timeSlots` | array | Time slots: `["morning", "afternoon", "evening"]` |
| `maxHoursPerWeek` | integer | Max hours per week (1-168) |
| `acceptsUrgentOffers` | boolean | Accepts urgent offers |
| `yearsOfExperience` | integer | Years of experience (0-100) |
| `hasCleaningSupplies` | boolean | Has cleaning supplies |
| `comfortableWithPets` | boolean | Comfortable with pets |
| `serviceRadius` | integer | Service radius in km (1-1000) |
| `agreedToTerms` | boolean | Agreed to terms |

### Optional Additional Data (Cleaners)

| Field | Type | Description |
|-------|------|-------------|
| `idCard` | file | ID card image |
| `avatar` | file | Profile avatar image |
| `previousServices` | array | Service IDs: `[{"type": "service", "id": 1}]` |
| `preferredServices` | array | Service IDs: `[{"type": "service", "id": 2}]` |

## Important Notes

### OAuth Token vs Sanctum Token

**OAuth Token** (from Google/Facebook/Apple):
- ❌ Do NOT store this long-term
- ❌ Do NOT use for API requests
- ✅ Only used once to authenticate with Hygeia API

**Sanctum Token** (from Hygeia API):
- ✅ Store securely in Keychain/SharedPreferences
- ✅ Use for all subsequent API requests
- ✅ Send in `Authorization: Bearer {token}` header

### User Flow Recommendations

#### For Clients
1. Show OAuth buttons on login screen
2. User taps "Sign in with Google"
3. Authenticate with Google
4. Send token to API
5. Receive Sanctum token
6. Navigate to home screen

#### For Cleaners
1. Show "Register as Cleaner" flow
2. Collect all required cleaner data first (multi-step form)
3. On final step, show OAuth buttons
4. Authenticate with selected provider
5. Send token + all collected data to API
6. Receive Sanctum token
7. Navigate to cleaner dashboard

### Security Best Practices

1. **Store Sanctum token in Keychain** (iOS) or Encrypted SharedPreferences (Android)
2. **Never log OAuth or Sanctum tokens** in production
3. **Use HTTPS only** for all API requests
4. **Validate SSL certificates**
5. **Handle token expiration** and refresh when needed

### Testing

For development/testing, you can use these test credentials:

```swift
// Don't use real OAuth in unit tests
// Mock the OAuth flow and API responses
```

## Error Handling

### Common Errors

| Error Code | Meaning | Solution |
|------------|---------|----------|
| `OAUTH_INVALID_TOKEN` | OAuth token invalid or expired | Get fresh token from provider |
| `OAUTH_ERROR` | General OAuth error | Check logs, verify provider config |
| 422 | Validation error | Check request format, required fields |
| 500 | Server error | Contact backend team |

### Example Error Handling

```swift
func handleOAuthResponse(data: Data?, error: Error?) {
    if let error = error {
        print("Network error: \(error)")
        showAlert("Connection failed. Please try again.")
        return
    }
    
    guard let data = data,
          let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any] else {
        showAlert("Invalid response from server")
        return
    }
    
    // Check for errors
    if let errors = json["errors"] as? [[String: Any]],
       let firstError = errors.first,
       let indicator = firstError["indicator"] as? String {
        
        switch indicator {
        case "OAUTH_INVALID_TOKEN":
            showAlert("Authentication failed. Please try again.")
        case "OAUTH_ERROR":
            showAlert("An error occurred. Please try again later.")
        default:
            showAlert("Something went wrong. Please try again.")
        }
        return
    }
    
    // Success - extract token
    if let dataNode = json["data"] as? [String: Any],
       let attributes = dataNode["attributes"] as? [String: Any],
       let token = attributes["token"] as? String {
        storeAccessToken(token)
        navigateToHome()
    }
}
```

## Sample Projects

Check the mobile team's repository for complete example implementations:
- iOS: `ios-oauth-example`
- Android: `android-oauth-example`

## Support

### Questions?

Contact the backend team:
- Slack: #backend-support
- Email: backend@hygeia.com

### Resources

- OAuth Implementation Guide: `docs/OAUTH_IMPLEMENTATION.md`
- Environment Setup: `docs/ENV_OAUTH_SETUP.md`
- API Documentation: [Scramble API Docs](https://your-api.com/docs/api)

---

**Last Updated**: 2025-11-01  
**API Version**: v1  
**Supported Providers**: Google, Facebook, Apple