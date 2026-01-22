<?php

use App\Support\ActionLogger;
use App\Http\Middleware\LogRequests;
use App\Http\Middleware\RefreshRememberToken;
use App\Http\Middleware\SetLocationContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register request logging middleware for web routes
        $middleware->web(append: [
            LogRequests::class,
            SetLocationContext::class,
        ]);
        
        // NOTE: RefreshRememberToken middleware removed because Auth::login() 
        // regenerates session and invalidates CSRF token.
        // Session lifetime is already set to 20 years in config/session.php,
        // so users will stay logged in without needing to refresh remember token.
        
        // Exclude dashboard-api and reports-api routes from CSRF verification
        // scan-api routes now require CSRF token (session no longer regenerates)
        $middleware->validateCsrfTokens(except: [
            'dashboard-api/*',
            'reports-api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log all exceptions with context
        $exceptions->report(function (\Throwable $e) {
            ActionLogger::logError($e, [
                'request_data' => request()?->except(['password', 'password_confirmation', '_token']),
            ]);
        });
        
        // Make sure API routes return JSON even if they're in web.php
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            // Check if request expects JSON or is an API route
            return $request->expectsJson() 
                || $request->is('scan-api/*')
                || $request->is('dashboard-api/*')
                || $request->is('reports-api/*')
                || $request->is('birthday-reservations-api/*')
                || $request->is('children/data')
                || $request->is('children-search')
                || $request->is('guardians-search')
                || $request->is('guardians-data')
                || $request->is('sessions/data');
        });
    })->create();
