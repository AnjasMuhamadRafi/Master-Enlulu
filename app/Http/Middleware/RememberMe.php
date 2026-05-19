<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RememberMe
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Jika user sudah authenticated dan remember me di-set
        if ($request->user() && $request->hasCookie('remember_me')) {
            // Keep the cookie alive
            return $next($request)->cookie(
                'remembered_email',
                auth()->user()->email,
                525600,
                '/',
                null,
                false,
                false
            );
        }

        return $next($request);
    }
}
