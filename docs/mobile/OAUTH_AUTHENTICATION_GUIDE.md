# OAuth Authentication Guide for Mobile Developers

## 📱 Overview

This guide explains how to implement OAuth authentication (Google, Facebook, Apple) in your mobile app for the Hygeia platform. The authentication flow is designed to provide a seamless user experience while handling both new user registration and existing user login.

---

## 🎯 Table of Contents

1. [Quick Start](#quick-start)
2. [Authentication Flow](#authentication-flow)
3. [API Endpoints](#api-endpoints)
4. [Implementation Examples](#implementation-examples)
5. [Error Handling](#error-handling)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

---

## Quick Start

### The Two-Step OAuth Flow

1. **Check if user exists** → `POST /api/v1/auth/oauth/check`
2. **Login or Register** → `POST /api/v1/auth/oauth/login`

This approach provides the best user experience:
- **Existing users** don't need to select their role again
- **New users** are guided through role selection and profile setup
- **Returning users** can login with one tap

---

## Authentication Flow

### 🔄 Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    Mobile App                                    │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      │ 1. User taps "Sign in with Google/Facebook/Apple"
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│              OAuth Provider (Google/Facebook/Apple)              │
│                  User authenticates with provider                │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      │ 2. Provider returns OAuth access token
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Mobile App                                    │
│              Receives OAuth token from provider                  │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      │ 3. POST /api/v1/auth/oauth/check
                      │    { provider, oauthToken }
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Backend API                                   │
│         Validates token & checks if user exists                  │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ├─────────────────────┬─────────────────────┐
                      │                     │                     │
          ┌───────────▼──────────┐ ┌────────▼────────┐ ┌────────▼────────┐
          │ User Exists          │ │ Email Exists    │ │ New User        │
          │ (has OAuth link)     │ │ (no OAuth link) │ │ (doesn't exist) │
          └───────────┬──────────┘ └────────┬────────┘ └────────┬────────┘
                      │                     │                     │
                      │ exists: true        │ exists: true        │ exists: false
                      │ role: "client"      │ role: "cleaner"     │ email: "..."
                      │                     │                     │ name: "..."
                      │                     │                     │
          ┌───────────▼──────────┐ ┌────────▼────────┐ ┌────────▼────────┐
          │ Login Directly       │ │ Login & Link    │ │ Show Role       │
          │                      │ │ OAuth Provider  │ │ Selection       │
          └───────────┬──────────┘ └────────┬────────┘ └────────┬────────┘
                      │                     │                     │
                      │ 4a. POST /api/v1/auth/oauth/login         │
                      │     { provider, oauthToken, role }        │
                      │                     │                     │
                      └─────────────────────┴─────────────────────┘
                                            │
                      ┌─────────────────────▼─────────────────────┐
                      │          Backend Returns Sanctum Token     │
                      │     { token: "...", role: "client" }       │
                      └─────────────────────┬─────────────────────┘
                                            │
                      ┌─────────────────────▼─────────────────────┐
                      │    Mobile App Stores Token & Navigates     │
                      │            to Main Application             │
                      └────────────────────────────────────────────┘
```

### 📝 Step-by-Step Process

#### Step 1: Authenticate with OAuth Provider

**iOS Example (Google):**
```swift
import GoogleSignIn

func signInWithGoogle() {
    GIDSignIn.sharedInstance.signIn(with: config, presenting: self) { user, error in
        guard let user = user,
              let idToken = user.authentication.idToken else {
            // Handle error
            return
        }
        
        // idToken is your OAuth access token
        self.checkUserRegistration(provider: "google", oauthToken: idToken)
    }
}
```

**Android Example (Google):**
```kotlin
import com.google.android.gms.auth.api.signin.GoogleSignIn

fun signInWithGoogle() {
    val signInIntent = googleSignInClient.signInIntent
    startActivityForResult(signInIntent, RC_SIGN_IN)
}

override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
    super.onActivityResult(requestCode, resultCode, data)
    
    if (requestCode == RC_SIGN_IN) {
        val task = GoogleSignIn.getSignedInAccountFromIntent(data)
        val account = task.getResult(ApiException::class.java)
        val idToken = account?.idToken
        
        // idToken is your OAuth access token
        checkUserRegistration("google", idToken)
    }
}
```

#### Step 2: Check if User Exists

**Endpoint:** `POST /api/v1/auth/oauth/check`

**Request:**
```json
{
  "data": {
    "attributes": {
      "provider": "google",
      "oauthToken": "ya29.a0AfH6SMBx..."
    }
  }
}
```

**Response (User Exists):**
```json
{
  "data": {
    "type": "oauth-check",
    "id": "google:123456789",
    "attributes": {
      "exists": true,
      "provider": "google",
      "email": "john@example.com",
      "name": "John Doe",
      "role": "client",
      "userId": 42
    }
  }
}
```

**Response (User Doesn't Exist):**
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

#### Step 3: Handle Response

**Existing User Path:**
```swift
func handleCheckResponse(_ response: OAuthCheckResponse) {
    if response.exists {
        // User exists - login directly
        // The backend already knows their role
        loginWithOAuth(
            provider: response.provider,
            oauthToken: storedOAuthToken,
            role: response.role // Use the role from check response
        )
    } else {
        // New user - show role selection
        showRoleSelection(
            email: response.email,
            name: response.name,
            provider: response.provider,
            oauthToken: storedOAuthToken
        )
    }
}
```

**New User Path:**
```swift
func handleRoleSelection(selectedRole: Role) {
    if selectedRole == .client {
        // Simple client registration
        registerClient(
            provider: provider,
            oauthToken: oauthToken
        )
    } else {
        // Cleaner needs additional info
        showCleanerProfileForm(
            provider: provider,
            oauthToken: oauthToken
        )
    }
}
```

#### Step 4: Login or Register

**Endpoint:** `POST /api/v1/auth/oauth/login`

**Request (Client):**
```json
{
  "data": {
    "attributes": {
      "provider": "google",
      "oauthToken": "ya29.a0AfH6SMBx...",
      "role": "client"
    },
    "relationships": {
      "deviceTokens": {
        "data": {
          "attributes": {
            "token": "firebase-device-token-here"
          }
        }
      }
    }
  }
}
```

**Request (Cleaner - with additional data):**
```json
{
  "data": {
    "attributes": {
      "provider": "facebook",
      "oauthToken": "EAABw...",
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
        "agreedToTerms": true
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
          {"type": "service", "id": 1}
        ]
      },
      "deviceTokens": {
        "data": {
          "attributes": {
            "token": "firebase-device-token-here"
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
    "id": "1|abc123def456...",
    "attributes": {
      "token": "1|abc123def456...",
      "role": "client"
    }
  }
}
```

#### Step 5: Store Token & Navigate

```swift
func handleLoginResponse(_ response: AuthTokenResponse) {
    // Store token securely in Keychain
    KeychainHelper.save(token: response.token, key: "auth_token")
    KeychainHelper.save(role: response.role, key: "user_role")
    
    // Configure API client with token
    APIClient.shared.setAuthToken(response.token)
    
    // Navigate to main app
    if response.role == "client" {
        navigateToClientHome()
    } else {
        navigateToCleanerHome()
    }
}
```

---

## API Endpoints

### 1. Check OAuth User Status

**Endpoint:** `POST /api/v1/auth/oauth/check`

**Purpose:** Check if a user is registered before completing login/registration flow.

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "data": {
    "attributes": {
      "provider": "google|facebook|apple",
      "oauthToken": "string"
    }
  }
}
```

**Success Response (200 OK):**

*User Exists:*
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

*User Doesn't Exist:*
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

**Error Response (401 Unauthorized):**
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

**Validation Errors (422 Unprocessable Entity):**
```json
{
  "errors": [
    {
      "status": "422",
      "code": "ValidationError",
      "title": "Validation failed",
      "detail": "The OAuth provider is required.",
      "source": {
        "pointer": "/data/attributes/provider"
      }
    }
  ]
}
```

---

### 2. OAuth Login/Register

**Endpoint:** `POST /api/v1/auth/oauth/login`

**Purpose:** Complete OAuth login or registration process.

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body (Client):**
```json
{
  "data": {
    "attributes": {
      "provider": "google|facebook|apple",
      "oauthToken": "string",
      "role": "client"
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

**Request Body (Cleaner):**
```json
{
  "data": {
    "attributes": {
      "provider": "google|facebook|apple",
      "oauthToken": "string",
      "role": "cleaner",
      "additionalData": {
        "phone": "+1234567890",
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
            "token": "firebase-device-token"
          }
        }
      }
    }
  }
}
```

**Success Response (200 OK):**
```json
{
  "data": {
    "type": "auth-token",
    "id": "1|abc123def456...",
    "attributes": {
      "token": "1|abc123def456...",
      "role": "client"
    }
  }
}
```

**Error Responses:** Same as OAuth Check endpoint

---

## Implementation Examples

### iOS Implementation (Swift)

#### Complete OAuth Flow Manager

```swift
import Foundation
import GoogleSignIn

class OAuthManager {
    
    static let shared = OAuthManager()
    private let apiClient = APIClient.shared
    
    // MARK: - Google Sign In
    
    func signInWithGoogle(presenting viewController: UIViewController, completion: @escaping (Result<AuthToken, Error>) -> Void) {
        
        guard let clientID = FirebaseApp.app()?.options.clientID else { return }
        let config = GIDConfiguration(clientID: clientID)
        
        GIDSignIn.sharedInstance.signIn(with: config, presenting: viewController) { [weak self] user, error in
            guard let self = self else { return }
            
            if let error = error {
                completion(.failure(error))
                return
            }
            
            guard let idToken = user?.authentication.idToken else {
                completion(.failure(OAuthError.noToken))
                return
            }
            
            // Step 1: Check if user exists
            self.checkUserStatus(provider: "google", oauthToken: idToken) { result in
                switch result {
                case .success(let checkResult):
                    self.handleCheckResult(
                        checkResult,
                        provider: "google",
                        oauthToken: idToken,
                        viewController: viewController,
                        completion: completion
                    )
                case .failure(let error):
                    completion(.failure(error))
                }
            }
        }
    }
    
    // MARK: - Check User Status
    
    func checkUserStatus(provider: String, oauthToken: String, completion: @escaping (Result<OAuthCheckResponse, Error>) -> Void) {
        
        let endpoint = "/api/v1/auth/oauth/check"
        let parameters: [String: Any] = [
            "data": [
                "attributes": [
                    "provider": provider,
                    "oauthToken": oauthToken
                ]
            ]
        ]
        
        apiClient.post(endpoint, parameters: parameters) { (result: Result<OAuthCheckResponse, Error>) in
            completion(result)
        }
    }
    
    // MARK: - Handle Check Result
    
    private func handleCheckResult(
        _ checkResult: OAuthCheckResponse,
        provider: String,
        oauthToken: String,
        viewController: UIViewController,
        completion: @escaping (Result<AuthToken, Error>) -> Void
    ) {
        if checkResult.exists {
            // User exists - login directly with their role
            self.loginWithOAuth(
                provider: provider,
                oauthToken: oauthToken,
                role: checkResult.role!,
                completion: completion
            )
        } else {
            // New user - show role selection
            DispatchQueue.main.async {
                self.showRoleSelection(
                    email: checkResult.email ?? "",
                    name: checkResult.name ?? "",
                    provider: provider,
                    oauthToken: oauthToken,
                    viewController: viewController,
                    completion: completion
                )
            }
        }
    }
    
    // MARK: - Show Role Selection
    
    private func showRoleSelection(
        email: String,
        name: String,
        provider: String,
        oauthToken: String,
        viewController: UIViewController,
        completion: @escaping (Result<AuthToken, Error>) -> Void
    ) {
        let alert = UIAlertController(
            title: "Welcome to Hygeia!",
            message: "How would you like to use Hygeia?",
            preferredStyle: .actionSheet
        )
        
        alert.addAction(UIAlertAction(title: "I need cleaning services (Client)", style: .default) { _ in
            self.registerClient(
                provider: provider,
                oauthToken: oauthToken,
                completion: completion
            )
        })
        
        alert.addAction(UIAlertAction(title: "I want to provide cleaning services (Cleaner)", style: .default) { _ in
            // Show cleaner profile form
            self.showCleanerProfileForm(
                email: email,
                name: name,
                provider: provider,
                oauthToken: oauthToken,
                viewController: viewController,
                completion: completion
            )
        })
        
        alert.addAction(UIAlertAction(title: "Cancel", style: .cancel))
        
        viewController.present(alert, animated: true)
    }
    
    // MARK: - Register Client
    
    func registerClient(
        provider: String,
        oauthToken: String,
        deviceToken: String? = nil,
        completion: @escaping (Result<AuthToken, Error>) -> Void
    ) {
        var parameters: [String: Any] = [
            "data": [
                "attributes": [
                    "provider": provider,
                    "oauthToken": oauthToken,
                    "role": "client"
                ]
            ]
        ]
        
        // Add device token if available
        if let token = deviceToken ?? UserDefaults.standard.string(forKey: "fcm_token") {
            parameters["data"] = [
                "attributes": parameters["data"] as! [String: Any],
                "relationships": [
                    "deviceTokens": [
                        "data": [
                            "attributes": [
                                "token": token
                            ]
                        ]
                    ]
                ]
            ]
        }
        
        let endpoint = "/api/v1/auth/oauth/login"
        apiClient.post(endpoint, parameters: parameters) { (result: Result<AuthTokenResponse, Error>) in
            switch result {
            case .success(let response):
                completion(.success(response.data.attributes))
            case .failure(let error):
                completion(.failure(error))
            }
        }
    }
    
    // MARK: - Register Cleaner
    
    func registerCleaner(
        provider: String,
        oauthToken: String,
        cleanerData: CleanerRegistrationData,
        deviceToken: String? = nil,
        completion: @escaping (Result<AuthToken, Error>) -> Void
    ) {
        var additionalData: [String: Any] = [
            "phone": cleanerData.phone,
            "availableDays": cleanerData.availableDays,
            "timeSlots": cleanerData.timeSlots,
            "maxHoursPerWeek": cleanerData.maxHoursPerWeek,
            "acceptsUrgentOffers": cleanerData.acceptsUrgentOffers,
            "yearsOfExperience": cleanerData.yearsOfExperience,
            "hasCleaningSupplies": cleanerData.hasCleaningSupplies,
            "comfortableWithPets": cleanerData.comfortableWithPets,
            "serviceRadius": cleanerData.serviceRadius,
            "agreedToTerms": cleanerData.agreedToTerms
        ]
        
        var parameters: [String: Any] = [
            "data": [
                "attributes": [
                    "provider": provider,
                    "oauthToken": oauthToken,
                    "role": "cleaner",
                    "additionalData": additionalData
                ]
            ]
        ]
        
        // Add services if provided
        var relationships: [String: Any] = [:]
        
        if !cleanerData.previousServiceIds.isEmpty {
            relationships["previousServices"] = [
                "data": cleanerData.previousServiceIds.map { ["type": "service", "id": $0] }
            ]
        }
        
        if !cleanerData.preferredServiceIds.isEmpty {
            relationships["preferredServices"] = [
                "data": cleanerData.preferredServiceIds.map { ["type": "service", "id": $0] }
            ]
        }
        
        // Add device token
        if let token = deviceToken ?? UserDefaults.standard.string(forKey: "fcm_token") {
            relationships["deviceTokens"] = [
                "data": [
                    "attributes": [
                        "token": token
                    ]
                ]
            ]
        }
        
        if !relationships.isEmpty {
            var data = parameters["data"] as! [String: Any]
            data["relationships"] = relationships
            parameters["data"] = data
        }
        
        let endpoint = "/api/v1/auth/oauth/login"
        apiClient.post(endpoint, parameters: parameters) { (result: Result<AuthTokenResponse, Error>) in
            switch result {
            case .success(let response):
                completion(.success(response.data.attributes))
            case .failure(let error):
                completion(.failure(error))
            }
        }
    }
    
    // MARK: - Login (for existing users)
    
    func loginWithOAuth(
        provider: String,
        oauthToken: String,
        role: String,
        deviceToken: String? = nil,
        completion: @escaping (Result<AuthToken, Error>) -> Void
    ) {
        // Same as registerClient but role is already known
        registerClient(provider: provider, oauthToken: oauthToken, deviceToken: deviceToken, completion: completion)
    }
    
    // MARK: - Helper Methods
    
    private func showCleanerProfileForm(
        email: String,
        name: String,
        provider: String,
        oauthToken: String,
        viewController: UIViewController,
        completion: @escaping (Result<AuthToken, Error>) -> Void
    ) {
        // Present a form view controller to collect cleaner data
        let cleanerFormVC = CleanerProfileFormViewController()
        cleanerFormVC.email = email
        cleanerFormVC.name = name
        cleanerFormVC.onSubmit = { cleanerData in
            self.registerCleaner(
                provider: provider,
                oauthToken: oauthToken,
                cleanerData: cleanerData,
                completion: completion
            )
        }
        
        let nav = UINavigationController(rootViewController: cleanerFormVC)
        viewController.present(nav, animated: true)
    }
}

// MARK: - Data Models

struct OAuthCheckResponse: Codable {
    let data: OAuthCheckData
    
    var exists: Bool { data.attributes.exists }
    var email: String? { data.attributes.email }
    var name: String? { data.attributes.name }
    var role: String? { data.attributes.role }
    var userId: Int? { data.attributes.userId }
}

struct OAuthCheckData: Codable {
    let type: String
    let id: String
    let attributes: OAuthCheckAttributes
}

struct OAuthCheckAttributes: Codable {
    let exists: Bool
    let provider: String
    let email: String?
    let name: String?
    let role: String?
    let userId: Int?
}

struct AuthTokenResponse: Codable {
    let data: AuthTokenData
}

struct AuthTokenData: Codable {
    let type: String
    let id: String
    let attributes: AuthToken
}

struct AuthToken: Codable {
    let token: String
    let role: String
}

struct CleanerRegistrationData {
    let phone: String
    let availableDays: [String]
    let timeSlots: [String]
    let maxHoursPerWeek: Int
    let acceptsUrgentOffers: Bool
    let yearsOfExperience: Int
    let hasCleaningSupplies: Bool
    let comfortableWithPets: Bool
    let serviceRadius: Int
    let agreedToTerms: Bool
    let previousServiceIds: [Int]
    let preferredServiceIds: [Int]
}

enum OAuthError: Error {
    case noToken
    case invalidResponse
}
```

#### Usage Example

```swift
// In your login view controller
@IBAction func googleSignInTapped(_ sender: UIButton) {
    OAuthManager.shared.signInWithGoogle(presenting: self) { result in
        switch result {
        case .success(let authToken):
            // Save token and navigate
            KeychainHelper.save(token: authToken.token, key: "auth_token")
            KeychainHelper.save(role: authToken.role, key: "user_role")
            
            if authToken.role == "client" {
                self.navigateToClientHome()
            } else {
                self.navigateToCleanerHome()
            }
            
        case .failure(let error):
            self.showError(error.localizedDescription)
        }
    }
}
```

---

### Android Implementation (Kotlin)

#### Complete OAuth Flow Manager

```kotlin
import com.google.android.gms.auth.api.signin.GoogleSignIn
import com.google.android.gms.auth.api.signin.GoogleSignInAccount
import com.google.android.gms.auth.api.signin.GoogleSignInOptions
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class OAuthManager(private val context: Context) {
    
    private val apiClient = ApiClient.getInstance(context)
    private val googleSignInClient by lazy {
        val gso = GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
            .requestIdToken(context.getString(R.string.default_web_client_id))
            .requestEmail()
            .build()
        GoogleSignIn.getClient(context, gso)
    }
    
    // MARK: - Google Sign In
    
    fun getGoogleSignInIntent(): Intent {
        return googleSignInClient.signInIntent
    }
    
    suspend fun handleGoogleSignInResult(data: Intent?): Result<AuthToken> = withContext(Dispatchers.IO) {
        try {
            val task = GoogleSignIn.getSignedInAccountFromIntent(data)
            val account = task.getResult(ApiException::class.java)
            val idToken = account?.idToken ?: throw Exception("No ID token")
            
            // Step 1: Check if user exists
            val checkResult = checkUserStatus("google", idToken)
            
            // Step 2: Handle check result
            handleCheckResult(checkResult, "google", idToken)
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    // MARK: - Check User Status
    
    suspend fun checkUserStatus(provider: String, oauthToken: String): OAuthCheckResponse = withContext(Dispatchers.IO) {
        val request = OAuthCheckRequest(
            data = OAuthCheckRequestData(
                attributes = OAuthCheckRequestAttributes(
                    provider = provider,
                    oauthToken = oauthToken
                )
            )
        )
        
        apiClient.checkOAuthUser(request)
    }
    
    // MARK: - Handle Check Result
    
    private suspend fun handleCheckResult(
        checkResult: OAuthCheckResponse,
        provider: String,
        oauthToken: String
    ): Result<AuthToken> {
        return if (checkResult.data.attributes.exists) {
            // User exists - login directly
            loginWithOAuth(provider, oauthToken, checkResult.data.attributes.role!!)
        } else {
            // New user - need to show role selection
            // Return a special result that triggers role selection UI
            Result.failure(RoleSelectionRequiredException(checkResult))
        }
    }
    
    // MARK: - Register Client
    
    suspend fun registerClient(
        provider: String,
        oauthToken: String,
        deviceToken: String? = null
    ): Result<AuthToken> = withContext(Dispatchers.IO) {
        try {
            val fcmToken = deviceToken ?: getFCMToken()
            
            val request = OAuthLoginRequest(
                data = OAuthLoginRequestData(
                    attributes = OAuthLoginRequestAttributes(
                        provider = provider,
                        oauthToken = oauthToken,
                        role = "client"
                    ),
                    relationships = fcmToken?.let {
                        OAuthLoginRequestRelationships(
                            deviceTokens = DeviceTokenRelationship(
                                data = DeviceTokenData(
                                    attributes = DeviceTokenAttributes(token = it)
                                )
                            )
                        )
                    }
                )
            )
            
            val response = apiClient.oauthLogin(request)
            Result.success(response.data.attributes)
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    // MARK: - Register Cleaner
    
    suspend fun registerCleaner(
        provider: String,
        oauthToken: String,
        cleanerData: CleanerRegistrationData,
        deviceToken: String? = null
    ): Result<AuthToken> = withContext(Dispatchers.IO) {
        try {
            val fcmToken = deviceToken ?: getFCMToken()
            
            val additionalData = AdditionalData(
                phone = cleanerData.phone,
                availableDays = cleanerData.availableDays,
                timeSlots = cleanerData.timeSlots,
                maxHoursPerWeek = cleanerData.maxHoursPerWeek,
                acceptsUrgentOffers = cleanerData.acceptsUrgentOffers,
                yearsOfExperience = cleanerData.yearsOfExperience,
                hasCleaningSupplies = cleanerData.hasCleaningSupplies,
                comfortableWithPets = cleanerData.comfortableWithPets,
                serviceRadius = cleanerData.serviceRadius,
                agreedToTerms = cleanerData.agreedToTerms
            )
            
            val relationships = mutableMapOf<String, Any>()
            
            if (cleanerData.previousServiceIds.isNotEmpty()) {
                relationships["previousServices"] = ServiceRelationship(
                    data = cleanerData.previousServiceIds.map { 
                        ServiceData(type = "service", id = it) 
                    }
                )
            }
            
            if (cleanerData.preferredServiceIds.isNotEmpty()) {
                relationships["preferredServices"] = ServiceRelationship(
                    data = cleanerData.preferredServiceIds.map { 
                        ServiceData(type = "service", id = it) 
                    }
                )
            }
            
            if (fcmToken != null) {
                relationships["deviceTokens"] = DeviceTokenRelationship(
                    data = DeviceTokenData(
                        attributes = DeviceTokenAttributes(token = fcmToken)
                    )
                )
            }
            
            val request = OAuthLoginRequest(
                data = OAuthLoginRequestData(
                    attributes = OAuthLoginRequestAttributes(
                        provider = provider,
                        oauthToken = oauthToken,
                        role = "cleaner",
                        additionalData = additionalData
                    ),
                    relationships = if (relationships.isNotEmpty()) 
                        OAuthLoginRequestRelationships.fromMap(relationships) 
                        else null
                )
            )
            
            val response = apiClient.oauthLogin(request)
            Result.success(response.data.attributes)
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
    
    // MARK: - Login (for existing users)
    
    suspend fun loginWithOAuth(
        provider: String,
        oauthToken: String,
        role: String,
        deviceToken: String? = null
    ): Result<AuthToken> {
        return registerClient(provider, oauthToken, deviceToken)
    }
    
    // MARK: - Helper Methods
    
    private fun getFCMToken(): String? {
        // Retrieve FCM token from your Firebase setup
        return null // Implement based on your Firebase setup
    }
}

// MARK: - Data Classes

data class OAuthCheckResponse(
    val data: OAuthCheckData
)

data class OAuthCheckData(
    val type: String,
    val id: String,
    val attributes: OAuthCheckAttributes
)

data class OAuthCheckAttributes(
    val exists: Boolean,
    val provider: String,
    val email: String?,
    val name: String?,
    val role: String?,
    val userId: Int?
)

data class OAuthCheckRequest(
    val data: OAuthCheckRequestData
)

data class OAuthCheckRequestData(
    val attributes: OAuthCheckRequestAttributes
)

data class OAuthCheckRequestAttributes(
    val provider: String,
    val oauthToken: String
)

data class AuthTokenResponse(
    val data: AuthTokenData
)

data class AuthTokenData(
    val type: String,
    val id: String,
    val attributes: AuthToken
)

data class AuthToken(
    val token: String,
    val role: String
)

data class CleanerRegistrationData(
    val phone: String,
    val availableDays: List<String>,
    val timeSlots: List<String>,
    val maxHoursPerWeek: Int,
    val acceptsUrgentOffers: Boolean,
    val yearsOfExperience: Int,
    val hasCleaningSupplies: Boolean,
    val comfortableWithPets: Boolean,
    val serviceRadius: Int,
    val agreedToTerms: Boolean,
    val previousServiceIds: List<Int>,
    val preferredServiceIds: List<Int>
)

class RoleSelectionRequiredException(val checkResult: OAuthCheckResponse) : Exception()
```

#### Usage Example

```kotlin
// In your login activity
class LoginActivity : AppCompatActivity() {
    
    private val oauthManager by lazy { OAuthManager(this) }
    private val RC_SIGN_IN = 9001
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)
        
        btnGoogleSignIn.setOnClickListener {
            signInWithGoogle()
        }
    }
    
    private fun signInWithGoogle() {
        val signInIntent = oauthManager.getGoogleSignInIntent()
        startActivityForResult(signInIntent, RC_SIGN_IN)
    }
    
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        
        if (requestCode == RC_SIGN_IN) {
            lifecycleScope.launch {
                try {
                    val result = oauthManager.handleGoogleSignInResult(data)
                    result.onSuccess { authToken ->
                        // Save token and navigate
                        saveAuthToken(authToken)
                        navigateToHome(authToken.role)
                    }.onFailure { error ->
                        if (error is RoleSelectionRequiredException) {
                            // Show role selection dialog
                            showRoleSelection(error.checkResult)
                        } else {
                            showError(error.message)
                        }
                    }
                } catch (e: Exception) {
                    showError(e.message)
                }
            }
        }
    }
    
    private fun showRoleSelection(checkResult: OAuthCheckResponse) {
        AlertDialog.Builder(this)
            .setTitle("Welcome to Hygeia!")
            .setMessage("How would you like to use Hygeia?")
            .setPositiveButton("I need cleaning services (Client)") { _, _ ->
                lifecycleScope.launch {
                    // Register as client
                    val result = oauthManager.registerClient(
                        provider = checkResult.data.attributes.provider,
                        oauthToken = storedOAuthToken
                    )
                    result.onSuccess { authToken ->
                        saveAuthToken(authToken)
                        navigateToHome(authToken.role)
                    }
                }
            }
            .setNegativeButton("I want to provide services (Cleaner)") { _, _ ->
                // Show cleaner profile form
                showCleanerProfileForm(checkResult)
            }
            .show()
    }
    
    private fun saveAuthToken(authToken: AuthToken) {
        val prefs = getSharedPreferences("app_prefs", MODE_PRIVATE)
        prefs.edit().apply {
            putString("auth_token", authToken.token)
            putString("user_role", authToken.role)
            apply()
        }
    }
    
    private fun navigateToHome(role: String) {
        val intent = when (role) {
            "client" -> Intent(this, ClientHomeActivity::class.java)
            "cleaner" -> Intent(this, CleanerHomeActivity::class.java)
            else -> return
        }
        startActivity(intent)
        finish()
    }
}
```

---

## Error Handling

### Common Error Scenarios

#### 1. Invalid OAuth Token

**Status:** 401 Unauthorized

**Indicator:** `OAUTH_INVALID_TOKEN`

**Cause:** 
- Token expired
- Token revoked
- Invalid token format
- Token from wrong environment (dev vs prod)

**Handling:**
```swift
if error.indicator == "OAUTH_INVALID_TOKEN" {
    // Token is invalid - restart OAuth flow
    showAlert("Please try signing in again")
    // Clear any cached OAuth data
    clearOAuthCache()
}
```

#### 2. Validation Errors

**Status:** 422 Unprocessable Entity

**Cause:**
- Missing required fields
- Invalid field format
- Invalid enum values

**Handling:**
```swift
if error.status == 422 {
    // Show specific validation errors
    for validationError in error.errors {
        showError(validationError.detail)
    }
}
```

#### 3. Network Errors

**Handling:**
```swift
do {
    let response = try await checkUserStatus(provider: provider, oauthToken: token)
    // Handle response
} catch URLError.notConnectedToInternet {
    showAlert("Please check your internet connection")
} catch URLError.timedOut {
    showAlert("Request timed out. Please try again")
} catch {
    showAlert("An error occurred. Please try again")
}
```

#### 4. Server Errors

**Status:** 500 Internal Server Error

**Handling:**
```swift
if error.status == 500 {
    // Log error for debugging
    logger.error("Server error during OAuth: \(error)")
    
    // Show user-friendly message
    showAlert("Something went wrong. Please try again later")
}
```

### Error Response Structure

All errors follow this format:

```json
{
  "errors": [
    {
      "status": "401",
      "code": "Unauthorized",
      "title": "OAuth authentication failed",
      "detail": "Invalid OAuth token or state mismatch",
      "indicator": "OAUTH_INVALID_TOKEN",
      "source": {
        "pointer": "/data/attributes/oauthToken"
      }
    }
  ]
}
```

---

## Best Practices

### 1. Token Management

✅ **DO:**
- Store Sanctum auth token securely in Keychain (iOS) or EncryptedSharedPreferences (Android)
- Clear OAuth tokens after exchange - only keep Sanctum token
- Include token in all authenticated API requests via `Authorization: Bearer {token}` header
- Handle token expiration gracefully

❌ **DON'T:**
- Store tokens in UserDefaults or SharedPreferences (not secure)
- Store OAuth tokens after successful exchange
- Hardcode tokens in your code

```swift
// Good - Secure storage
KeychainHelper.save(token: authToken.token, key: "auth_token")

// Bad - Insecure storage
UserDefaults.standard.set(authToken.token, forKey: "auth_token")
```

### 2. User Experience

✅ **DO:**
- Show loading indicators during OAuth flow
- Provide clear error messages
- Allow users to cancel OAuth flow
- Pre-fill form data from OAuth profile (name, email)
- Remember user's role choice (client/cleaner)

❌ **DON'T:**
- Block UI without loading indicators
- Show technical error messages to users
- Force users through OAuth if they cancel

### 3. Error Handling

✅ **DO:**
- Handle all error cases gracefully
- Log errors for debugging
- Provide retry mechanisms
- Show user-friendly error messages
- Implement timeout handling

❌ **DON'T:**
- Crash on errors
- Show raw error responses to users
- Ignore network errors

### 4. Testing

✅ **DO:**
- Test with real OAuth providers in development
- Test network failure scenarios
- Test token expiration
- Test role switching
- Test email linking scenarios

❌ **DON'T:**
- Only test happy path
- Skip error scenario testing
- Use production credentials in development

### 5. Security

✅ **DO:**
- Use HTTPS for all API calls
- Validate OAuth tokens server-side (backend handles this)
- Clear sensitive data from memory after use
- Use secure storage for tokens
- Implement certificate pinning in production

❌ **DON'T:**
- Log sensitive data (tokens, passwords)
- Store tokens in plain text
- Skip certificate validation

---

## Troubleshooting

### Issue: OAuth token is invalid

**Symptoms:** Getting `OAUTH_INVALID_TOKEN` error

**Solutions:**
1. Ensure you're using the correct OAuth token type (idToken for Google, accessToken for Facebook)
2. Check token hasn't expired
3. Verify OAuth provider configuration (client IDs, etc.)
4. Make sure backend and mobile app are using same OAuth app credentials

### Issue: User can't select role

**Symptoms:** Role selection not showing for new users

**Solutions:**
1. Check `exists: false` is properly handled
2. Verify UI logic for showing role selection
3. Ensure OAuth check request is completing successfully

### Issue: Email linking not working

**Symptoms:** User registers with OAuth but system doesn't recognize existing account

**Solutions:**
1. Verify email from OAuth provider matches exactly
2. Check backend logs for email matching logic
3. Ensure case-insensitive email comparison

### Issue: Cleaner registration fails

**Symptoms:** Getting validation errors for cleaner registration

**Solutions:**
1. Ensure all required fields are provided
2. Check field format (phone number, arrays, etc.)
3. Verify service IDs exist in database
4. Check `agreedToTerms` is set to `true`

### Issue: Device token not registered

**Symptoms:** Push notifications not working after OAuth

**Solutions:**
1. Verify FCM token is retrieved before OAuth call
2. Check token is included in request relationships
3. Ensure token is valid and not expired

---

## Summary

### Quick Reference

**Two-Step OAuth Flow:**
1. `POST /api/v1/auth/oauth/check` - Check if user exists
2. `POST /api/v1/auth/oauth/login` - Login or register

**Key Points:**
- ✅ Always check user status before login/registration
- ✅ Handle existing users and new users differently
- ✅ Store only Sanctum token securely
- ✅ Include device token for push notifications
- ✅ Provide clear UI for role selection
- ✅ Handle all error scenarios gracefully

**Client Registration:** Simple - just provider, token, and role

**Cleaner Registration:** Complex - requires additional profile data

**Email Linking:** Automatic - backend links OAuth to existing email

### Support

For questions or issues:
- Check backend logs for detailed error information
- Review OAuth provider documentation
- Test with backend OAuth test endpoints
- Contact backend team for API-specific issues

---

**Last Updated:** 2025-01-20  
**API Version:** v1  
**Status:** ✅ Production Ready