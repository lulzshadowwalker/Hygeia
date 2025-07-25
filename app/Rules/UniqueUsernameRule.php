<?php

namespace App\Rules;

use App\Contracts\ResponseBuilder;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UniqueUsernameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (User::where('username', $value)->exists()) {
            throw new HttpResponseException(
                app(ResponseBuilder::class)
                    ->error(
                        title: 'Username already exists',
                        detail: 'This username is already taken',
                        code: Response::HTTP_CONFLICT,
                        indicator: 'USERNAME_ALREADY_EXISTS'
                    )->build()
            );
        }
    }
}
