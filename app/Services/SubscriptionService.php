<?php

namespace App\Services;

use App\Models\Location;
use App\Models\LocationSubscription;
use Illuminate\Support\Collection;

class SubscriptionService
{
    public function getActiveSubscription(Location $location): ?LocationSubscription
    {
        return LocationSubscription::where('location_id', $location->id)
            ->where('starts_at', '<=', now())
            ->orderByDesc('starts_at')
            ->first();
    }

    public function getStatus(Location $location): string
    {
        $subscription = $this->getActiveSubscription($location);

        if ($subscription === null) {
            return 'none';
        }

        if ($subscription->expires_at >= now()) {
            return 'active';
        }

        if ($subscription->expires_at >= now()->subDays(7)) {
            return 'grace';
        }

        return 'expired';
    }

    public function getDaysUntilExpiry(Location $location): ?int
    {
        $subscription = $this->getActiveSubscription($location);

        if ($subscription === null) {
            return null;
        }

        return (int) now()->diffInDays($subscription->expires_at, false);
    }

    public function getExpiringSoon(int $days): Collection
    {
        $now = now();

        return LocationSubscription::where('starts_at', '<=', $now)
            ->whereRaw('starts_at = (SELECT MAX(ls2.starts_at) FROM location_subscriptions ls2 WHERE ls2.location_id = location_subscriptions.location_id AND ls2.starts_at <= ?)', [$now])
            ->where('expires_at', '>=', $now)
            ->whereDate('expires_at', $now->copy()->addDays($days)->toDateString())
            ->with(['location.company.users'])
            ->get();
    }
}
