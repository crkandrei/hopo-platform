<?php

namespace App\Events;

use App\Models\Location;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Location $location,
        public readonly ?SubscriptionPlan $plan,
        public readonly string $stripeEventId,
    ) {}
}
