<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;

#[Group('Authentication')]
class UsernameController extends Controller
{
    /**
     * Check username availability
     *
     * Check if a username is available.
     * A 200 response means the username is taken.
     * A 404 response means the username is available.
     *
     * @unauthenticated
     */
    public function show(string $username)
    {
        if (User::where('username', $username)->exists()) {
            return response()->noContent(200);
        }

        return response()->noContent(404);
    }
}
