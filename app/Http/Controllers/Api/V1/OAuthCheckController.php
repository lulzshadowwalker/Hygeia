<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OAuthCheckRequest;
use App\Http\Resources\V1\OAuthCheckResource;
use App\Models\OAuthProvider;
use App\Models\User;
use App\Services\OAuth\FirebaseAuthService;
use App\Support\OAuthCheckResult;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Authentication')]
class OAuthCheckController extends Controller
{
    public function __construct(
        protected FirebaseAuthService $firebaseAuthService,
    ) {
        //
    }

    /**
     * Check OAuth User Registration Status
     *
     * Check if a user is already registered with the given OAuth provider.
     * This endpoint should be called after the mobile app authenticates with the OAuth provider
     * but before calling the login/register endpoint.
     *
     * Use this to determine whether to:
     * - Login the user directly (if they exist)
     * - Show role selection screen (if they don't exist)
     *
     * The endpoint will check:
     * 1. If an OAuth provider record exists for this provider user ID
     * 2. If a user exists with the same email address
     *
     * Response will include:
     * - `exists: true` - User is registered, includes their role
     * - `exists: false` - User is not registered, includes OAuth profile data
     */
    public function check(OAuthCheckRequest $request): JsonResponse
    {
        $provider = $request->provider();
        $oauthToken = $request->oauthToken();

        if (! in_array($provider, ['google', 'facebook', 'apple'])) {
            throw new \InvalidArgumentException("Unsupported OAuth provider: {$provider}");
        }

        $this->firebaseAuthService->setProviderName($provider);

        try {
            // Get user info from OAuth provider
            $oauthUser = $this->firebaseAuthService->getUserFromToken($oauthToken);

            // Check if OAuth provider record exists
            $oauthProvider = OAuthProvider::where('provider', $provider)
                ->where('provider_user_id', $oauthUser->getId())
                ->with('user.roles')
                ->first();

            if ($oauthProvider) {
                // User exists with this OAuth provider
                $user = $oauthProvider->user;
                $role = $user->isClient ? Role::Client : Role::Cleaner;

                return OAuthCheckResource::make(
                    new OAuthCheckResult(
                        exists: true,
                        provider: $provider,
                        providerId: $oauthUser->getId(),
                        email: $oauthUser->getEmail(),
                        name: $oauthUser->getName(),
                        role: $role,
                        userId: $user->id,
                    ),
                )
                    ->response()
                    ->setStatusCode(200);
            }

            // Check if user exists with this email (not yet linked to OAuth)
            $user = null;
            if ($oauthUser->getEmail()) {
                $user = User::where('email', $oauthUser->getEmail())
                    ->with('roles')
                    ->first();
            }

            if ($user) {
                // User exists with email but not linked to this OAuth provider
                $role = $user->isClient ? Role::Client : Role::Cleaner;

                return OAuthCheckResource::make(
                    new OAuthCheckResult(
                        exists: true,
                        provider: $provider,
                        providerId: $oauthUser->getId(),
                        email: $oauthUser->getEmail(),
                        name: $oauthUser->getName(),
                        role: $role,
                        userId: $user->id,
                    ),
                )
                    ->response()
                    ->setStatusCode(200);
            }

            // User does not exist - return OAuth profile data for registration
            return OAuthCheckResource::make(
                new OAuthCheckResult(
                    exists: false,
                    provider: $provider,
                    providerId: $oauthUser->getId(),
                    email: $oauthUser->getEmail(),
                    name: $oauthUser->getName(),
                ),
            )
                ->response()
                ->setStatusCode(200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(
                [
                    'errors' => [
                        [
                            'status' => '401',
                            'code' => 'Unauthorized',
                            'title' => 'OAuth authentication failed',
                            'detail' => $e->getMessage(),
                            'indicator' => 'OAUTH_INVALID_TOKEN',
                        ],
                    ],
                ],
                401,
            );
        } catch (\Exception $e) {
            \Log::error('OAuth check error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'errors' => [
                        [
                            'status' => '500',
                            'code' => 'InternalServerError',
                            'title' => 'OAuth check failed',
                            'detail' => 'An error occurred while checking OAuth user status',
                            'indicator' => 'OAUTH_CHECK_ERROR',
                        ],
                    ],
                ],
                500,
            );
        }
    }
}
