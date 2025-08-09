<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Resources\V1\AuthTokenResource;
use App\Support\AccessToken;

class LoginController extends Controller
{
    public function store(LoginRequest $request)
    {
        $identifier = $request->identifier();
        $password = $request->password();

        if (
            !auth()->attempt(['email' => $identifier, 'password' => $password]) &&
            !auth()->attempt(['username' => $identifier, 'password' => $password])
        ) {
            //  TODO: We can handle the repsonse better
            throw new \Illuminate\Auth\AuthenticationException('Invalid credentials');
        }

        $user = auth()->user();

        if ($deviceToken = $request->deviceToken()) {
            $user->deviceTokens()->firstOrCreate(['token' => $deviceToken]);
        }

        $accessToken = $user->createToken(config('app.name'))->plainTextToken;

        return AuthTokenResource::make(
            new AccessToken(
                accessToken: $accessToken,
                role: $user->isClient ? Role::Client : Role::Cleaner,
            ),
        )->response()
            ->setStatusCode(200);
    }
}
