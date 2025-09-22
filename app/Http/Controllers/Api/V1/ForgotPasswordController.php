<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'data.attributes.identifier' => 'required|string',
        ]);

        $identifier = $request->input('data.attributes.identifier');

        if (! User::where('email', $identifier)->exists() && ! User::where('username', $identifier)->exists()) {
            // To prevent email enumeration attacks, we return a success response
            return response()->json([
                'data' => [
                    'type' => 'password-reset-request',
                    'attributes' => [
                        'message' => 'Password reset link sent to your email address.',
                    ],
                ],
            ], 200);
        }

        $email = User::where('email', $identifier)->orWhere('username', $identifier)->value('email');
        if (! $email) {
            throw ValidationException::withMessages([
                'data.attributes.identifier' => ['No user found with the provided identifier.'],
            ]);
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'data.attributes.identifier' => [__($status)],
            ]);
        }

        return response()->json([
            'data' => [
                'type' => 'password-reset-request',
                'attributes' => [
                    'message' => 'Password reset link sent to your email address.',
                ],
            ],
        ], 200);
    }
}
