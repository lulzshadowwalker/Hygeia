<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreLogoutRequest;

class LogoutController extends ApiController
{
    public function store(StoreLogoutRequest $request)
    {
        if ($deviceToken = $request->deviceToken()) {
            $request->user()->deviceTokens()->whereToken($deviceToken)?->delete();
        }

        $request->user()->currentAccessToken()?->delete();

        return $this->response->message('Logged out successfully')->build(200);
    }
}
