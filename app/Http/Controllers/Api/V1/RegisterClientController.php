<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRegisterClientRequest;
use App\Http\Resources\V1\AuthTokenResource;
use App\Models\User;
use App\Support\AccessToken;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\DB;

#[Group('Authentication')]
class RegisterClientController extends Controller
{
    /**
     * Register a new client
     *
     * Handle new client registration and issue an authentication token.
     */
    public function store(StoreRegisterClientRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name(),
                'username' => $request->username(),
                'email' => $request->email(),
                'password' => $request->password(),
            ]);

            if ($request->avatar()) {
                $user->addMedia($request->avatar())
                    ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            }

            if ($deviceToken = $request->deviceToken()) {
                $user->deviceTokens()->firstOrCreate(['token' => $deviceToken]);
            }

            $user->assignRole(Role::Client->value);
            $user->client()->create();

            $accessToken = $user->createToken(config('app.name'))->plainTextToken;

            return AuthTokenResource::make(
                new AccessToken(accessToken: $accessToken, role: Role::Client),
            )->response()->setStatusCode(201);
        });
    }
}
