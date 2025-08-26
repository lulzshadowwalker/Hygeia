<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to routes for users with the 'cleaner' role.
 */
class CleanerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        if (! $user->isCleaner) {
            throw new AccessDeniedException('Only cleaners can access this resource.');
        }

        return $next($request);
    }
}
