<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\WebhookStatus;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionPaymentFailed;
use App\Models\Location;
use App\Models\LocationSubscription;
use App\Models\StripeWebhookLog;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(private PaymentGatewayInterface $gateway)
    {
    }

    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        try {
            $event = $this->gateway->constructWebhookEvent($payload, $signature);
        } catch (\Throwable $e) {
            Log::warning('StripeWebhook: invalid signature', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        // Idempotency check
        if (StripeWebhookLog::where('stripe_event_id', $event->id)->exists()) {
            Log::info('StripeWebhook: duplicate event skipped', ['event_id' => $event->id]);
            return response('OK', 200);
        }

        $status       = WebhookStatus::Processed;
        $locationId   = null;
        $errorMessage = null;

        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $locationId = $this->handleCheckoutCompleted($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $locationId = $this->handlePaymentFailed($event->data->object, $event->id);
                    break;

                default:
                    Log::info('StripeWebhook: unhandled event type', ['type' => $event->type]);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('StripeWebhook: handler threw exception', [
                'event_id' => $event->id,
                'type'     => $event->type,
                'error'    => $e->getMessage(),
            ]);
            $status       = WebhookStatus::Failed;
            $errorMessage = $e->getMessage();
        }

        StripeWebhookLog::create([
            'stripe_event_id' => $event->id,
            'event_type'      => $event->type,
            'status'          => $status,
            'location_id'     => $locationId,
            'error_message'   => $errorMessage,
            'processed_at'    => now(),
        ]);

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(object $session): ?int
    {
        $locationId = $session->metadata->location_id ?? null;
        $planId     = $session->metadata->plan_id ?? null;

        if (!$locationId || !$planId) {
            Log::warning('StripeWebhook: checkout.session.completed missing metadata', [
                'session_id' => $session->id,
            ]);
            return null;
        }

        $location = Location::find($locationId);
        $plan     = SubscriptionPlan::find($planId);

        if (!$location || !$plan) {
            Log::error('StripeWebhook: location or plan not found', [
                'location_id' => $locationId,
                'plan_id'     => $planId,
            ]);
            return $locationId;
        }

        $subscription = LocationSubscription::create([
            'location_id'      => $location->id,
            'plan_id'          => $plan->id,
            'plan_type'        => 'standard',
            'starts_at'        => now(),
            'expires_at'       => now()->addMonths($plan->duration_months)->setTime(2, 0, 0),
            'price_paid'       => $session->amount_total / 100,
            'payment_method'   => 'card',
            'payment_source'   => 'stripe',
            'stripe_session_id'  => $session->id,
            'stripe_payment_id'  => $session->payment_intent,
            'created_by'       => null,
        ]);

        SubscriptionActivated::dispatch($subscription, 'stripe');

        Log::info('StripeWebhook: subscription activated', [
            'subscription_id' => $subscription->id,
            'location_id'     => $location->id,
            'plan_id'         => $plan->id,
        ]);

        return $location->id;
    }

    private function handlePaymentFailed(object $paymentIntent, string $eventId): ?int
    {
        $locationId = $paymentIntent->metadata->location_id ?? null;
        $planId     = $paymentIntent->metadata->plan_id ?? null;

        $location = $locationId ? Location::find($locationId) : null;
        $plan     = $planId ? SubscriptionPlan::find($planId) : null;

        if ($location) {
            SubscriptionPaymentFailed::dispatch($location, $plan, $eventId);
        }

        Log::warning('StripeWebhook: payment_intent.payment_failed', [
            'payment_intent_id' => $paymentIntent->id,
            'location_id'       => $locationId,
        ]);

        return $locationId;
    }
}
