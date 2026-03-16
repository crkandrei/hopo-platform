<?php

namespace App\Listeners;

use App\Events\SubscriptionActivated;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class LogSubscriptionActivation
{
    public function handle(SubscriptionActivated $event): void
    {
        try {
            AuditLog::create([
                'location_id' => $event->subscription->location_id,
                'user_id'     => $event->subscription->created_by,
                'action'      => 'subscription_activated',
                'entity_type' => 'LocationSubscription',
                'entity_id'   => $event->subscription->id,
                'data_before' => null,
                'data_after'  => [
                    'source'     => $event->source,
                    'plan_id'    => $event->subscription->plan_id,
                    'expires_at' => $event->subscription->expires_at?->toISOString(),
                    'price_paid' => $event->subscription->price_paid,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('LogSubscriptionActivation: failed to write audit log', [
                'subscription_id' => $event->subscription->id,
                'error'           => $e->getMessage(),
            ]);
        }
    }
}
