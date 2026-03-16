<?php

namespace App\Events;

use App\Models\LocationSubscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly LocationSubscription $subscription,
        public readonly string $source, // 'manual' | 'stripe'
    ) {}
}
