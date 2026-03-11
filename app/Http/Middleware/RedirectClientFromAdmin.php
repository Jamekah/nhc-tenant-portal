<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectClientFromAdmin
{
    /**
     * Redirect client (tenant) users away from admin panel routes.
     * They are allowed to authenticate via Filament's login form,
     * but LoginResponse handles redirecting them to the portal.
     * This middleware catches any direct admin panel access attempts.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->hasRole('client')) {
            return redirect()->route('portal.dashboard');
        }

        return $next($request);
    }
}
