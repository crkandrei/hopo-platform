<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class CheckLocationSubscription
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $excludedRoutes = [
            'login',
            'logout',
            'change-password',
            'subscription.blocked',
            'booking.*',
            'legal.*',
            'location-context.*',
            'admin.subscriptions.*',
            'checkout.*',
            'payment.*',
            'stripe.*',
        ];

        if ($request->routeIs(...$excludedRoutes)) {
            return $next($request);
        }

        // Cover unnamed routes that must also be excluded (e.g. POST /change-password, POST /booking/{slug})
        $excludedPaths = [
            'change-password',
            'booking/*',
        ];

        if ($request->is(...$excludedPaths)) {
            return $next($request);
        }

        $location = app('current.location');

        if (!$location) {
            return redirect()->route('subscription.blocked');
        }

        $status = $this->subscriptionService->getStatus($location);

        if ($status === 'active') {
            return $next($request);
        }

        if ($status === 'grace') {
            $subscription = $this->subscriptionService->getActiveSubscription($location);
            $daysInGrace = (int) now()->diffInDays($subscription->expires_at);
            $daysRemaining = max(0, 7 - $daysInGrace);

            app()->instance('subscription.grace', true);
            app()->instance('subscription.grace_days', $daysRemaining);
            app()->instance('subscription.grace_expires_at', $subscription->expires_at);
            app()->instance('subscription.grace_location', $location->name);

            return $next($request);
        }

        return redirect()->route('subscription.blocked');
    }
}
