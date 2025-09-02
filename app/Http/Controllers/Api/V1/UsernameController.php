<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;

class UsernameController extends Controller
{
    /**
     * Check if a username is available.
     */
    public function show(string $username)
    {
        if (User::where('username', $username)->exists()) {
            return response()->noContent(200);
        }

        return response()->noContent(404);
    }
}
