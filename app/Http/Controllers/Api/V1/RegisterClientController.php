<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRegisterClientRequest;
use App\Http\Resources\V1\AuthTokenResource;
use App\Models\User;
use App\Support\AccessToken;
use Illuminate\Support\Facades\DB;

class RegisterClientController extends Controller
{
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

            $user->assignRole(Role::Client->value);
            $user->client()->create();

            $accessToken = $user->createToken(config('app.name'))->plainTextToken;
            return AuthTokenResource::make(
                new AccessToken(accessToken: $accessToken, role: Role::Client),
            )->response()->setStatusCode(201);
        });
    }
}
