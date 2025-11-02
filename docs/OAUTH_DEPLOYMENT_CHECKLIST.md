# OAuth Deployment Checklist

## Pre-Deployment Checklist

### 1. Environment Variables

#### Development/Staging
- [ ] `GOOGLE_CLIENT_ID` set
- [ ] `GOOGLE_CLIENT_SECRET` set
- [ ] `GOOGLE_REDIRECT_URI` set (http://localhost:8000 for dev)
- [ ] `FACEBOOK_CLIENT_ID` set
- [ ] `FACEBOOK_CLIENT_SECRET` set
- [ ] `FACEBOOK_REDIRECT_URI` set
- [ ] `APPLE_CLIENT_ID` set
- [ ] `APPLE_KEY_ID` set (if using private key method)
- [ ] `APPLE_TEAM_ID` set (if using private key method)
- [ ] `APPLE_PRIVATE_KEY` path set (absolute path to .p8 file)
- [ ] `APPLE_REDIRECT_URI` set

#### Production
- [ ] All production OAuth credentials obtained
- [ ] Production environment variables configured
- [ ] HTTPS redirect URIs configured
- [ ] .p8 file stored securely (outside web root)
- [ ] .p8 file permissions set to 600 or 400

### 2. OAuth Provider Setup

#### Google Cloud Console
- [ ] Project created
- [ ] OAuth 2.0 Client ID created
- [ ] Authorized redirect URIs added
- [ ] Credentials downloaded and saved securely
- [ ] API scopes configured (email, profile)

#### Facebook Developers
- [ ] App created
- [ ] Facebook Login product added
- [ ] Valid OAuth Redirect URIs configured
- [ ] App reviewed and approved (if required)
- [ ] App in production mode (not development)

#### Apple Developer
- [ ] App ID created
- [ ] Services ID created and configured
- [ ] Sign in with Apple enabled
- [ ] Domains and redirect URIs verified
- [ ] Private key (.p8) generated and downloaded
- [ ] Key ID and Team ID recorded

### 3. Database

- [ ] Migrations run: `php artisan migrate`
- [ ] `oauth_providers` table exists
- [ ] `users.password` is nullable
- [ ] `users.email_verified_at` in fillable array
- [ ] Database backups configured

### 4. Code Verification

- [ ] `composer require laravel/socialite` installed
- [ ] `composer require socialiteproviders/google` installed
- [ ] `composer require socialiteproviders/facebook` installed
- [ ] `composer require socialiteproviders/apple` installed
- [ ] Socialite providers registered in `AppServiceProvider`
- [ ] Services config updated (`config/services.php`)
- [ ] Route added: `POST /api/v1/auth/oauth/login`

### 5. Testing

- [ ] All 12 OAuth tests passing
- [ ] Manual testing with Google OAuth
- [ ] Manual testing with Facebook OAuth
- [ ] Manual testing with Apple OAuth
- [ ] Client registration tested
- [ ] Cleaner registration tested
- [ ] Device token handling tested
- [ ] Email linking tested

### 6. Documentation

- [ ] Mobile team provided with API documentation
- [ ] OAuth flow documented
- [ ] Error codes documented
- [ ] Example requests/responses shared

## Deployment Steps

### Step 1: Backup
```bash
# Backup database
php artisan backup:run

# Tag current version
git tag -a v1.0.0-pre-oauth -m "Before OAuth deployment"
```

### Step 2: Deploy Code
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 3: Run Migrations
```bash
# Run migrations
php artisan migrate --force

# Verify tables
php artisan tinker
>>> DB::table('oauth_providers')->count();
```

### Step 4: Verify Configuration
```bash
# Check environment variables
php artisan tinker
>>> config('services.google.client_id');
>>> config('services.facebook.client_id');
>>> config('services.apple.client_id');
```

### Step 5: Smoke Test
```bash
# Test OAuth endpoint is accessible
curl -X POST https://your-api.com/api/v1/auth/oauth/login \
  -H "Content-Type: application/json" \
  -d '{
    "data": {
      "attributes": {
        "provider": "google",
        "oauthToken": "invalid-token",
        "role": "client"
      }
    }
  }'

# Should return 401 with OAUTH_INVALID_TOKEN error
```

### Step 6: Monitor
- [ ] Enable error logging
- [ ] Monitor OAuth authentication errors
- [ ] Track successful OAuth logins
- [ ] Monitor database for new oauth_providers records

## Post-Deployment Checklist

### Immediate (Day 1)

- [ ] Verify OAuth login works for all providers
- [ ] Check error logs for OAuth-related errors
- [ ] Verify device tokens are being stored
- [ ] Test client registration flow
- [ ] Test cleaner registration flow
- [ ] Verify email linking works
- [ ] Check Sanctum token generation

### Short-term (Week 1)

- [ ] Monitor OAuth success/failure rates
- [ ] Collect mobile team feedback
- [ ] Address any reported issues
- [ ] Optimize slow queries (if any)
- [ ] Review security logs

### Long-term (Month 1)

- [ ] Analyze OAuth usage patterns
- [ ] Review token expiration handling
- [ ] Plan token refresh implementation
- [ ] Consider adding more providers
- [ ] User feedback review

## Rollback Plan

If issues occur, follow this rollback procedure:

### Step 1: Disable OAuth Endpoint
```php
// In routes/api_v1.php, comment out:
// Route::post('/auth/oauth/login', [OAuthLoginController::class, 'store'])
//     ->name('api.v1.auth.oauth.login');
```

### Step 2: Rollback Migrations
```bash
php artisan migrate:rollback --step=2
```

### Step 3: Revert Code
```bash
git revert HEAD
# or
git reset --hard <previous-commit-hash>
```

### Step 4: Notify Stakeholders
- [ ] Notify mobile team
- [ ] Update status page
- [ ] Communicate timeline for fix

## Monitoring & Alerts

### Key Metrics to Monitor

- OAuth login success rate
- OAuth login failure rate
- Average response time for OAuth endpoint
- Database query performance
- Error rates by provider (Google/Facebook/Apple)

### Alert Thresholds

- [ ] Alert if OAuth failure rate > 10%
- [ ] Alert if response time > 3 seconds
- [ ] Alert if database errors occur
- [ ] Alert if provider-specific errors spike

### Log Locations

```bash
# Application logs
tail -f storage/logs/laravel.log | grep "OAuth"

# Nginx/Apache logs
tail -f /var/log/nginx/access.log | grep "oauth"
```

## Security Checklist

- [ ] OAuth tokens stored encrypted
- [ ] OAuth tokens marked as hidden in model
- [ ] HTTPS enforced for all OAuth requests
- [ ] Rate limiting enabled on OAuth endpoint
- [ ] CORS configured correctly
- [ ] .env file not in version control
- [ ] .p8 file permissions set correctly (600)
- [ ] .p8 file not in version control
- [ ] Secrets not hardcoded anywhere
- [ ] Regular security audits scheduled

## Performance Checklist

- [ ] Database indexes on oauth_providers table verified
- [ ] Query optimization tested
- [ ] Response time < 2 seconds for OAuth login
- [ ] Caching strategy in place (if needed)
- [ ] CDN configured (if applicable)

## Compliance Checklist

- [ ] Privacy policy updated to mention OAuth providers
- [ ] Terms of service updated
- [ ] GDPR compliance verified (if applicable)
- [ ] Data retention policy defined
- [ ] User consent mechanism in place

## Mobile App Coordination

- [ ] Mobile team has latest API documentation
- [ ] Mobile team has test credentials
- [ ] Staging environment provided for testing
- [ ] Deployment timeline communicated
- [ ] Support channel established

## Documentation Updates

- [ ] API documentation updated
- [ ] Internal wiki updated
- [ ] Mobile integration guide shared
- [ ] Troubleshooting guide created
- [ ] FAQ document created

## Support Preparation

- [ ] Support team trained on OAuth flow
- [ ] Common issues documented
- [ ] Escalation path defined
- [ ] Monitoring dashboard created
- [ ] On-call schedule updated

## Success Criteria

### Technical
- ✅ All tests passing
- ✅ OAuth login success rate > 95%
- ✅ Response time < 2 seconds
- ✅ Zero critical errors in first week

### Business
- ✅ Users can register via OAuth
- ✅ Mobile team integrated successfully
- ✅ User satisfaction maintained
- ✅ Support ticket volume manageable

## Notes

### Important Contacts

- **Backend Lead**: [Name] - [Email]
- **Mobile Lead**: [Name] - [Email]
- **DevOps**: [Name] - [Email]
- **Product Manager**: [Name] - [Email]

### Useful Commands

```bash
# Check OAuth providers in database
php artisan tinker
>>> App\Models\OAuthProvider::count();
>>> App\Models\OAuthProvider::with('user')->get();

# Test OAuth service
php artisan tinker
>>> $service = app(\App\Services\OAuth\GoogleOAuthService::class);
>>> $service->getProviderName();

# Clear all caches
php artisan optimize:clear

# Run specific OAuth tests
php artisan test --filter OAuthLoginControllerTest
```

## Sign-off

- [ ] Backend Team Lead: _________________ Date: _______
- [ ] Mobile Team Lead: __________________ Date: _______
- [ ] DevOps: ___________________________ Date: _______
- [ ] Product Manager: __________________ Date: _______

---

**Deployment Date**: ______________  
**Deployed By**: ______________  
**Version**: ______________  
**Environment**: ☐ Staging ☐ Production