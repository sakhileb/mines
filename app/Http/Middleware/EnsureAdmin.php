<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to admin-only routes.
 * Must be used inside an authenticated route group.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->hasRole('admin'), 403, 'Admin access required.');

        return $next($request);
    }
}
