<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !$request->session()->has('last_login_recorded')) {
            auth()->user()->update(['last_login_at' => now()]);
            $request->session()->put('last_login_recorded', true);
        }

        return $next($request);
    }
}
