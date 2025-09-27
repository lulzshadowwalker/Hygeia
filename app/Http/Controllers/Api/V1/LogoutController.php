<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreLogoutRequest;
use Dedoc\Scramble\Attributes\Group;

#[Group('Authentication')]
class LogoutController extends ApiController
{
    /**
     * Log out
     *
     * Handle user logout and revoke the current access token.
     */
    public function store(StoreLogoutRequest $request)
    {
        if ($deviceToken = $request->deviceToken()) {
            $request->user()->deviceTokens()->whereToken($deviceToken)?->delete();
        }

        if (method_exists($request->user()->currentAccessToken(), 'delete')) {
            $request->user()->currentAccessToken()?->delete();
        }

        return $this->response->message('Logged out successfully')->build(200);
    }
}
