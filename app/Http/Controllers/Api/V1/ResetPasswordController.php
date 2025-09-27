<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

#[Group('Authentication')]
class ResetPasswordController extends Controller
{
    /**
     * Reset password
     *
     * @unauthenticated
     */
    public function store(Request $request)
    {
        $request->validate([
            'data.attributes.token' => 'required|string',
            'data.attributes.email' => 'required|email',
            'data.attributes.password' => 'required|string|min:8|confirmed',
        ], [
            'data.attributes.password.confirmed' => 'The password confirmation does not match.',
        ]);

        $credentials = [
            'token' => $request->input('data.attributes.token'),
            'email' => $request->input('data.attributes.email'),
            'password' => $request->input('data.attributes.password'),
            'password_confirmation' => $request->input('data.attributes.password_confirmation'),
        ];

        $status = Password::reset($credentials, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'data.attributes.email' => [__($status)],
            ]);
        }

        return response()->json([
            'data' => [
                'type' => 'password-reset',
                'attributes' => [
                    'message' => 'Password has been reset successfully.',
                ],
            ],
        ], 200);
    }
}
