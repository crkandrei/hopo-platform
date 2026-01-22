<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocationContext
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Bind pentru acces ușor în cod
            app()->instance('current.user', $user);
            app()->instance('current.location', $user->location);
            app()->instance('current.company', $user->company ?? $user->location?->company);
        }
        
        return $next($request);
    }
}
