<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to routes for users with the 'cleaner' role.
 */
class ClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        if (! $user->isClient) {
            throw new AuthorizationException(
                'Only clients can access this resource.',
                Response::HTTP_FORBIDDEN
            );
        }

        return $next($request);
    }
}
