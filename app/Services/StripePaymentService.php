<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Location;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService implements PaymentGatewayInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    public function createCheckoutSession(SubscriptionPlan $plan, Location $location, User $user): string
    {
        try {
            $session = Session::create([
                'mode'          => 'payment',
                'currency'      => config('stripe.currency'),
                'customer_email' => $user->email,
                'line_items'    => [
                    [
                        'price'    => $plan->stripe_price_id,
                        'quantity' => 1,
                    ],
                ],
                'metadata'      => [
                    'location_id' => $location->id,
                    'plan_id'     => $plan->id,
                    'user_id'     => $user->id,
                ],
                'success_url'   => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'    => route('checkout.plans'),
            ]);

            return $session->url;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe createCheckoutSession failed', [
                'plan_id'     => $plan->id,
                'location_id' => $location->id,
                'error'       => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function createPlan(SubscriptionPlan $plan): array
    {
        try {
            $product = Product::create([
                'name'        => $plan->name,
                'description' => "Abonament {$plan->name} — {$plan->duration_months} luni",
            ]);

            $price = Price::create([
                'product'     => $product->id,
                'unit_amount' => (int) round($plan->price * 100),
                'currency'    => config('stripe.currency'),
            ]);

            return [
                'product_id' => $product->id,
                'price_id'   => $price->id,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe createPlan failed', [
                'plan_id' => $plan->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function updatePlanName(SubscriptionPlan $plan): void
    {
        if (!$plan->stripe_product_id) {
            return;
        }

        try {
            Product::update($plan->stripe_product_id, [
                'name'        => $plan->name,
                'description' => "Abonament {$plan->name} — {$plan->duration_months} luni",
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe updatePlanName failed', [
                'plan_id' => $plan->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function archivePlan(SubscriptionPlan $plan): void
    {
        try {
            if ($plan->stripe_price_id) {
                Price::update($plan->stripe_price_id, ['active' => false]);
            }

            if ($plan->stripe_product_id) {
                Product::update($plan->stripe_product_id, ['active' => false]);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe archivePlan failed', [
                'plan_id' => $plan->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function constructWebhookEvent(string $payload, string $signature): object
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('stripe.webhook_secret')
        );
    }
}
