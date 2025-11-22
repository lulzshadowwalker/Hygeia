# Mobile Developer Documentation

Welcome to the Hygeia mobile development documentation! This folder contains comprehensive guides for integrating with the Hygeia backend API.

## 📚 Documentation Index

### OAuth Authentication

| Document | Description | When to Use |
|----------|-------------|-------------|
| [**OAuth Authentication Guide**](./OAUTH_AUTHENTICATION_GUIDE.md) | Complete implementation guide with detailed code examples for iOS and Android | When implementing OAuth for the first time or need detailed examples |
| [**OAuth Quick Reference**](./OAUTH_QUICK_REFERENCE.md) | Quick reference guide with API endpoints, request/response examples, and checklists | When you need quick answers or API format reference |

## 🚀 Getting Started

### For New Developers

1. Start with [OAuth Authentication Guide](./OAUTH_AUTHENTICATION_GUIDE.md)
2. Review the complete authentication flow
3. Implement the two-step OAuth flow (check → login)
4. Test with all three providers (Google, Facebook, Apple)
5. Keep [Quick Reference](./OAUTH_QUICK_REFERENCE.md) handy while coding

### For Experienced Developers

1. Jump to [Quick Reference](./OAUTH_QUICK_REFERENCE.md)
2. Review API endpoints and request formats
3. Implement using the code snippets provided
4. Refer to full guide for edge cases and troubleshooting

## 🎯 OAuth Authentication Overview

### The Two-Step Flow

```
1. User taps "Sign in with Provider"
2. Get OAuth token from provider
3. POST /api/v1/auth/oauth/check ← Check if user exists
4. If exists: Login directly
   If new: Show role selection → Register
5. POST /api/v1/auth/oauth/login ← Complete authentication
6. Store Sanctum token → Navigate to app
```

### Key Benefits

- ✅ **Better UX** - Existing users login with one tap
- ✅ **Role Discovery** - Backend tells you the user's role
- ✅ **Email Linking** - Automatic linking to existing accounts
- ✅ **Multi-Provider** - User can link Google, Facebook, AND Apple

## 📍 API Endpoints

### Check User Status
```
POST /api/v1/auth/oauth/check
```
Determines if user is already registered.

### Login/Register
```
POST /api/v1/auth/oauth/login
```
Complete OAuth authentication flow.

## 🔑 Supported OAuth Providers

- **Google** - Sign in with Google
- **Facebook** - Sign in with Facebook
- **Apple** - Sign in with Apple

## 👥 User Roles

- **Client** - Service consumers (simple registration)
- **Cleaner** - Service providers (requires additional profile data)

## 📱 Platform Support

### iOS
- Swift implementation examples
- Google Sign-In SDK
- Facebook Login SDK
- Apple Sign In (native)

### Android
- Kotlin implementation examples
- Google Sign-In SDK
- Facebook SDK
- Apple Sign In (if supported)

## 🔐 Security Best Practices

1. **Token Storage**
   - iOS: Use Keychain
   - Android: Use EncryptedSharedPreferences
   - Never use UserDefaults/SharedPreferences

2. **HTTPS Only**
   - All API calls must use HTTPS
   - No plain HTTP in production

3. **Token Lifecycle**
   - Clear OAuth tokens after exchange
   - Store only Sanctum token
   - Handle token expiration

4. **Certificate Pinning**
   - Implement in production
   - Prevents man-in-the-middle attacks

## ⚠️ Common Pitfalls

### ❌ Don't Do This

```swift
// Bad - Insecure storage
UserDefaults.standard.set(token, forKey: "auth_token")

// Bad - Hardcoded values
let apiUrl = "http://api.example.com" // Use HTTPS!

// Bad - Ignoring errors
try? apiClient.login() // Handle errors properly
```

### ✅ Do This Instead

```swift
// Good - Secure storage
KeychainHelper.save(token: authToken, key: "auth_token")

// Good - HTTPS
let apiUrl = "https://api.example.com"

// Good - Proper error handling
do {
    try await apiClient.login()
} catch {
    handleError(error)
}
```

## 🧪 Testing Checklist

Before submitting your pull request:

- [ ] Tested with Google OAuth
- [ ] Tested with Facebook OAuth
- [ ] Tested with Apple OAuth
- [ ] Tested new user registration (Client)
- [ ] Tested new user registration (Cleaner)
- [ ] Tested existing user login
- [ ] Tested email linking scenario
- [ ] Tested invalid token handling
- [ ] Tested network error handling
- [ ] Tested offline behavior
- [ ] Tested token storage and retrieval
- [ ] Tested logout flow

## 📊 Response Format

All API responses use **JSON:API** format:

```json
{
  "data": {
    "type": "resource-type",
    "id": "resource-id",
    "attributes": {
      "key": "value"
    },
    "relationships": {
      "related": { }
    }
  }
}
```

## 🔧 Development Tips

1. **Use the Check Endpoint First**
   - Always call `/auth/oauth/check` before login
   - Provides better user experience
   - Reduces unnecessary API calls

2. **Cache OAuth Token Temporarily**
   - Keep OAuth token only during authentication flow
   - Clear after successful exchange for Sanctum token

3. **Pre-fill Forms**
   - Use data from OAuth check response
   - Reduces user input
   - Improves UX

4. **Show Loading States**
   - OAuth flows take 2-3 seconds
   - Show clear loading indicators
   - Allow cancellation

5. **Handle Offline Gracefully**
   - Detect network connectivity
   - Show appropriate error messages
   - Allow retry

## 🐛 Troubleshooting

### Issue: OAuth token is invalid

**Solution:** Ensure you're using fresh tokens from the provider. Tokens expire quickly.

### Issue: User can't login

**Solution:** Check backend logs. Verify OAuth provider configuration matches between mobile app and backend.

### Issue: Email linking not working

**Solution:** Verify email from OAuth provider matches exactly with existing user email.

### Issue: Cleaner registration fails

**Solution:** Ensure all required fields are provided. Check field validation in API documentation.

## 📞 Support

### For Questions

- **Backend API Issues**: Contact backend team
- **OAuth Provider Issues**: Check provider documentation
- **Mobile Implementation**: Review this documentation

### Resources

- **Backend Docs**: `../OAUTH_IMPLEMENTATION.md`
- **Environment Setup**: `../ENV_OAUTH_SETUP.md`
- **Deployment Info**: `../OAUTH_DEPLOYMENT_CHECKLIST.md`

### External Resources

- [Google Sign-In](https://developers.google.com/identity)
- [Facebook Login](https://developers.facebook.com/docs/facebook-login)
- [Apple Sign In](https://developer.apple.com/sign-in-with-apple/)
- [JSON:API Specification](https://jsonapi.org/)

## 🎓 Learning Path

### Beginner Path

1. Read OAuth Authentication Guide (complete)
2. Set up OAuth providers in your app
3. Implement check endpoint
4. Implement login endpoint
5. Test with all providers
6. Handle edge cases

### Intermediate Path

1. Review Quick Reference
2. Implement using code snippets
3. Add error handling
4. Implement token storage
5. Test all scenarios

### Advanced Path

1. Optimize API calls
2. Implement caching
3. Add offline support
4. Implement retry logic
5. Add analytics

## 📈 Performance Tips

1. **Minimize API Calls**
   - Use check endpoint efficiently
   - Cache appropriate data
   - Batch requests when possible

2. **Optimize Token Exchange**
   - Exchange OAuth token immediately
   - Don't store OAuth tokens long-term
   - Use token expiration wisely

3. **Handle Background States**
   - Save state on background
   - Restore on foreground
   - Handle token refresh

## 🔄 Migration Guide

### From Old Flow (Without Check Endpoint)

**Before:**
```
1. OAuth authentication
2. Ask user for role
3. Call login endpoint with role
```

**After:**
```
1. OAuth authentication
2. Call check endpoint
3. If exists: login directly
   If new: ask for role → login
```

**Benefits:**
- Existing users skip role selection
- Better user experience
- One less step for returning users

## 📝 Code Style Guidelines

### iOS (Swift)

```swift
// Use clear, descriptive names
func checkUserStatus(provider: String, oauthToken: String)

// Handle errors properly
do {
    try await apiCall()
} catch {
    handleError(error)
}

// Use strong typing
let checkResult: OAuthCheckResponse = ...
```

### Android (Kotlin)

```kotlin
// Use coroutines for async operations
suspend fun checkUserStatus(provider: String, oauthToken: String)

// Handle errors properly
try {
    apiCall()
} catch (e: Exception) {
    handleError(e)
}

// Use data classes
data class OAuthCheckResponse(...)
```

## 🎯 Success Criteria

Your OAuth implementation is complete when:

- ✅ All three providers work (Google, Facebook, Apple)
- ✅ Check endpoint is called before login
- ✅ Existing users can login with one tap
- ✅ New users see role selection
- ✅ Tokens are stored securely
- ✅ Errors are handled gracefully
- ✅ Offline behavior is appropriate
- ✅ All tests pass
- ✅ Code follows style guidelines
- ✅ Documentation is updated

## 🚀 Next Steps

1. Choose your documentation:
   - New to OAuth? → [Full Guide](./OAUTH_AUTHENTICATION_GUIDE.md)
   - Need quick answers? → [Quick Reference](./OAUTH_QUICK_REFERENCE.md)

2. Implement the two-step flow

3. Test thoroughly

4. Submit for review

5. Deploy and celebrate! 🎉

---

**Last Updated:** 2025-01-20  
**Version:** 1.0.0  
**Status:** ✅ Production Ready

---

## 📧 Contact

For questions or feedback:
- Backend Team: [Contact Info]
- Mobile Team Lead: [Contact Info]
- Documentation: Create an issue or PR

---

Happy coding! 🚀