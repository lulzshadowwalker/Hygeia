<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OAuthLoginRequest;
use App\Http\Resources\V1\AuthTokenResource;
use App\Services\OAuth\AppleOAuthService;
use App\Services\OAuth\FacebookOAuthService;
use App\Services\OAuth\GoogleOAuthService;
use App\Support\AccessToken;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Authentication')]
class OAuthLoginController extends Controller
{
    public function __construct(
        protected GoogleOAuthService $googleOAuthService,
        protected FacebookOAuthService $facebookOAuthService,
        protected AppleOAuthService $appleOAuthService,
    ) {}

    /**
     * OAuth Login/Register
     *
     * Handle OAuth authentication for Google, Facebook, and Apple.
     * This endpoint handles both login (for existing users) and registration (for new users).
     *
     * The mobile app should:
     * 1. Authenticate the user with the OAuth provider (Google/Facebook/Apple)
     * 2. Obtain an access token from the provider
     * 3. Send that token to this endpoint along with the desired role
     * 4. For cleaners, include additional required fields in additionalData
     */
    public function store(OAuthLoginRequest $request): JsonResponse
    {
        $provider = $request->provider();
        $oauthToken = $request->oauthToken();
        $role = $request->role();
        $additionalData = $request->additionalData();
        $deviceToken = $request->deviceToken();

        $oauthService = match ($provider) {
            'google' => $this->googleOAuthService,
            'facebook' => $this->facebookOAuthService,
            'apple' => $this->appleOAuthService,
            default => throw new \InvalidArgumentException("Unsupported OAuth provider: {$provider}"),
        };

        try {
            $user = $oauthService->handleOAuthCallback(
                oauthToken: $oauthToken,
                role: $role,
                additionalData: $additionalData,
                deviceToken: $deviceToken
            );

            $accessToken = $user->createToken(config('app.name'))->plainTextToken;

            $userRole = $user->isClient ? Role::Client : Role::Cleaner;

            return AuthTokenResource::make(
                new AccessToken(
                    accessToken: $accessToken,
                    role: $userRole,
                ),
            )->response()
                ->setStatusCode(200);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '401',
                        'code' => 'Unauthorized',
                        'title' => 'OAuth authentication failed',
                        'detail' => 'Invalid OAuth token or state mismatch',
                        'indicator' => 'OAUTH_INVALID_TOKEN',
                    ],
                ],
            ], 401);
        } catch (\Exception $e) {
            \Log::error('OAuth authentication error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'errors' => [
                    [
                        'status' => '500',
                        'code' => 'InternalServerError',
                        'title' => 'OAuth authentication failed',
                        'detail' => 'An error occurred during OAuth authentication',
                        'indicator' => 'OAUTH_ERROR',
                    ],
                ],
            ], 500);
        }
    }
}
