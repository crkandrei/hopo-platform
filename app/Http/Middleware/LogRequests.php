<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // List of paths to exclude from logging (frequent requests)
        $excludedPaths = [
            '/api/children/search',
            '/api/guardians/search',
            '/api/sessions/active',
            '/api/sessions/stats',
            '/api/scan/active-sessions',
        ];

        $shouldLog = !in_array($request->path(), $excludedPaths) &&
                     !$request->is('telescope/*') &&
                     !$request->is('_ignition/*');

        if ($shouldLog && $request->method() !== 'GET') {
            // Log POST, PUT, PATCH, DELETE requests
            $user = $request->user();
            $location = $user?->location;
            $company = $user?->company;

            Log::channel('actions')->info('Request logged', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'location_id' => $location?->id,
                'location_name' => $location?->name,
                'company_id' => $company?->id,
                'company_name' => $company?->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return $next($request);
    }
}





