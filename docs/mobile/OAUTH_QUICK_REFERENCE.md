# OAuth Quick Reference for Mobile Developers

## 🚀 Quick Start

### The Two-Step Flow

```
1. User taps "Sign in with Google/Facebook/Apple"
2. Mobile app gets OAuth token from provider
3. Call: POST /api/v1/auth/oauth/check
4. If exists: Login directly
   If new: Show role selection → Register
5. Call: POST /api/v1/auth/oauth/login
6. Store Sanctum token → Navigate to app
```

---

## 📍 API Endpoints

### 1. Check User Status

**Endpoint:** `POST /api/v1/auth/oauth/check`

**Request:**
```json
{
  "data": {
    "attributes": {
      "provider": "google",
      "oauthToken": "ya29.a0AfH6..."
    }
  }
}
```

**Response (Existing User):**
```json
{
  "data": {
    "type": "oauth-check",
    "id": "google:123456789",
    "attributes": {
      "exists": true,
      "provider": "google",
      "email": "user@example.com",
      "name": "User Name",
      "role": "client",
      "userId": 42
    }
  }
}
```

**Response (New User):**
```json
{
  "data": {
    "type": "oauth-check",
    "id": "google:123456789",
    "attributes": {
      "exists": false,
      "provider": "google",
      "email": "newuser@example.com",
      "name": "New User",
      "role": null,
      "userId": null
    }
  }
}
```

---

### 2. Login/Register

**Endpoint:** `POST /api/v1/auth/oauth/login`

**Request (Client):**
```json
{
  "data": {
    "attributes": {
      "provider": "google",
      "oauthToken": "ya29.a0AfH6...",
      "role": "client"
    },
    "relationships": {
      "deviceTokens": {
        "data": {
          "attributes": {
            "token": "firebase-token"
          }
        }
      }
    }
  }
}
```

**Request (Cleaner):**
```json
{
  "data": {
    "attributes": {
      "provider": "facebook",
      "oauthToken": "EAABw...",
      "role": "cleaner",
      "additionalData": {
        "phone": "+962792002802",
        "availableDays": ["monday", "tuesday"],
        "timeSlots": ["morning", "afternoon"],
        "maxHoursPerWeek": 40,
        "acceptsUrgentOffers": true,
        "yearsOfExperience": 3,
        "hasCleaningSupplies": true,
        "comfortableWithPets": true,
        "serviceRadius": 15,
        "agreedToTerms": true
      }
    },
    "relationships": {
      "previousServices": {
        "data": [
          {"type": "service", "id": 1}
        ]
      },
      "preferredServices": {
        "data": [
          {"type": "service", "id": 2}
        ]
      },
      "deviceTokens": {
        "data": {
          "attributes": {
            "token": "firebase-token"
          }
        }
      }
    }
  }
}
```

**Response:**
```json
{
  "data": {
    "type": "auth-token",
    "id": "1|abc123...",
    "attributes": {
      "token": "1|abc123def456...",
      "role": "client"
    }
  }
}
```

---

## 📱 Implementation Flow

### iOS (Swift)

```swift
// 1. Sign in with provider
func signInWithGoogle() {
    GIDSignIn.sharedInstance.signIn(with: config, presenting: self) { user, error in
        guard let idToken = user?.authentication.idToken else { return }
        
        // 2. Check if user exists
        checkUserStatus(provider: "google", oauthToken: idToken)
    }
}

// 2. Check user status
func checkUserStatus(provider: String, oauthToken: String) {
    let url = URL(string: "\(baseURL)/api/v1/auth/oauth/check")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("application/json", forHTTPHeaderField: "Content-Type")
    
    let body: [String: Any] = [
        "data": [
            "attributes": [
                "provider": provider,
                "oauthToken": oauthToken
            ]
        ]
    ]
    request.httpBody = try? JSONSerialization.data(withJSONObject: body)
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        // Parse response
        let checkResult = try? JSONDecoder().decode(OAuthCheckResponse.self, from: data!)
        
        if checkResult?.data.attributes.exists == true {
            // 3a. Login existing user
            self.loginWithOAuth(provider: provider, oauthToken: oauthToken, 
                               role: checkResult!.data.attributes.role!)
        } else {
            // 3b. Show role selection for new user
            self.showRoleSelection(provider: provider, oauthToken: oauthToken)
        }
    }.resume()
}

// 3. Login or Register
func loginWithOAuth(provider: String, oauthToken: String, role: String) {
    let url = URL(string: "\(baseURL)/api/v1/auth/oauth/login")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("application/json", forHTTPHeaderField: "Content-Type")
    
    let body: [String: Any] = [
        "data": [
            "attributes": [
                "provider": provider,
                "oauthToken": oauthToken,
                "role": role
            ],
            "relationships": [
                "deviceTokens": [
                    "data": [
                        "attributes": [
                            "token": getFCMToken()
                        ]
                    ]
                ]
            ]
        ]
    ]
    request.httpBody = try? JSONSerialization.data(withJSONObject: body)
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        let authResponse = try? JSONDecoder().decode(AuthTokenResponse.self, from: data!)
        
        // 4. Save token and navigate
        KeychainHelper.save(token: authResponse!.data.attributes.token, key: "auth_token")
        self.navigateToHome()
    }.resume()
}
```

### Android (Kotlin)

```kotlin
// 1. Sign in with provider
fun signInWithGoogle() {
    val signInIntent = googleSignInClient.signInIntent
    startActivityForResult(signInIntent, RC_SIGN_IN)
}

override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
    if (requestCode == RC_SIGN_IN) {
        val task = GoogleSignIn.getSignedInAccountFromIntent(data)
        val account = task.getResult(ApiException::class.java)
        val idToken = account?.idToken
        
        // 2. Check if user exists
        checkUserStatus("google", idToken!!)
    }
}

// 2. Check user status
suspend fun checkUserStatus(provider: String, oauthToken: String) {
    val request = OAuthCheckRequest(
        data = OAuthCheckRequestData(
            attributes = OAuthCheckRequestAttributes(
                provider = provider,
                oauthToken = oauthToken
            )
        )
    )
    
    val response = apiClient.checkOAuthUser(request)
    
    if (response.data.attributes.exists) {
        // 3a. Login existing user
        loginWithOAuth(provider, oauthToken, response.data.attributes.role!!)
    } else {
        // 3b. Show role selection for new user
        showRoleSelection(provider, oauthToken)
    }
}

// 3. Login or Register
suspend fun loginWithOAuth(provider: String, oauthToken: String, role: String) {
    val request = OAuthLoginRequest(
        data = OAuthLoginRequestData(
            attributes = OAuthLoginRequestAttributes(
                provider = provider,
                oauthToken = oauthToken,
                role = role
            ),
            relationships = OAuthLoginRequestRelationships(
                deviceTokens = DeviceTokenRelationship(
                    data = DeviceTokenData(
                        attributes = DeviceTokenAttributes(token = getFCMToken())
                    )
                )
            )
        )
    )
    
    val response = apiClient.oauthLogin(request)
    
    // 4. Save token and navigate
    saveAuthToken(response.data.attributes.token)
    navigateToHome()
}
```

---

## 🔑 Required Fields

### Client Registration
- ✅ `provider` (google/facebook/apple)
- ✅ `oauthToken` (from OAuth provider)
- ✅ `role` ("client")
- ⚪ `deviceToken` (optional but recommended)

### Cleaner Registration
- ✅ `provider`
- ✅ `oauthToken`
- ✅ `role` ("cleaner")
- ✅ `additionalData.phone`
- ✅ `additionalData.availableDays` (array of days)
- ✅ `additionalData.timeSlots` (array of slots)
- ✅ `additionalData.maxHoursPerWeek`
- ✅ `additionalData.acceptsUrgentOffers`
- ✅ `additionalData.yearsOfExperience`
- ✅ `additionalData.hasCleaningSupplies`
- ✅ `additionalData.comfortableWithPets`
- ✅ `additionalData.serviceRadius`
- ✅ `additionalData.agreedToTerms`
- ⚪ `previousServices` (optional)
- ⚪ `preferredServices` (optional)
- ⚪ `deviceToken` (optional but recommended)

---

## 📋 Valid Values

### Providers
- `google`
- `facebook`
- `apple`

### Roles
- `client`
- `cleaner`

### Available Days
- `sunday`
- `monday`
- `tuesday`
- `wednesday`
- `thursday`
- `friday`
- `saturday`

### Time Slots
- `morning`
- `afternoon`
- `evening`

---

## ⚠️ Error Codes

| Status | Indicator | Meaning |
|--------|-----------|---------|
| 401 | `OAUTH_INVALID_TOKEN` | OAuth token is invalid/expired |
| 422 | N/A | Validation error (missing/invalid fields) |
| 500 | `OAUTH_ERROR` | Server error |
| 500 | `OAUTH_CHECK_ERROR` | Error checking user status |

---

## 🔐 Security Checklist

- ✅ Use HTTPS for all API calls
- ✅ Store Sanctum token in Keychain (iOS) or EncryptedSharedPreferences (Android)
- ✅ Don't store OAuth tokens after exchange
- ✅ Include token in headers: `Authorization: Bearer {token}`
- ✅ Clear tokens on logout
- ✅ Handle token expiration

---

## 🎯 Testing Checklist

- ✅ New user registration (client)
- ✅ New user registration (cleaner)
- ✅ Existing user login
- ✅ Email linking (existing email, new OAuth)
- ✅ Invalid token handling
- ✅ Network error handling
- ✅ Validation error handling
- ✅ Role switching
- ✅ Device token registration

---

## 💡 Tips

1. **Always call check endpoint first** - Provides better UX
2. **Cache OAuth token temporarily** - Only until exchanged for Sanctum token
3. **Pre-fill forms** - Use data from OAuth check response
4. **Show loading indicators** - OAuth flows can take 2-3 seconds
5. **Handle offline gracefully** - Show appropriate error messages
6. **Test all providers** - Google, Facebook, and Apple have different behaviors

---

## 📞 Support

- **Full Documentation:** `docs/mobile/OAUTH_AUTHENTICATION_GUIDE.md`
- **Backend Team:** For API issues
- **OAuth Providers:** Check provider-specific documentation

---

**Last Updated:** 2025-01-20  
**API Version:** v1