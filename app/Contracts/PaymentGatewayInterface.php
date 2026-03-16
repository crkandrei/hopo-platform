<?php

namespace App\Contracts;

use App\Models\Location;
use App\Models\SubscriptionPlan;
use App\Models\User;

interface PaymentGatewayInterface
{
    /**
     * Create a hosted checkout session and return the redirect URL.
     */
    public function createCheckoutSession(SubscriptionPlan $plan, Location $location, User $user): string;

    /**
     * Create a product + price on the payment gateway.
     * Returns ['product_id' => ..., 'price_id' => ...].
     */
    public function createPlan(SubscriptionPlan $plan): array;

    /**
     * Update the product name on the payment gateway.
     */
    public function updatePlanName(SubscriptionPlan $plan): void;

    /**
     * Archive/deactivate a plan on the payment gateway.
     */
    public function archivePlan(SubscriptionPlan $plan): void;

    /**
     * Verify and parse an incoming webhook payload.
     */
    public function constructWebhookEvent(string $payload, string $signature): object;
}
