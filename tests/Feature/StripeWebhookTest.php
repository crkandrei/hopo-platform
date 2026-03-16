<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionPaymentFailed;
use App\Models\Company;
use App\Models\Location;
use App\Models\StripeWebhookLog;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    private function mockGateway(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function () {
            $mock = $this->createMock(PaymentGatewayInterface::class);

            $mock->method('constructWebhookEvent')
                ->willReturnCallback(function (string $payload, string $signature) {
                    if ($signature === 'invalid') {
                        throw new \UnexpectedValueException('Invalid signature');
                    }
                    return json_decode($payload);
                });

            return $mock;
        });
    }

    private function makeCheckoutCompletedPayload(int $locationId, int $planId, string $eventId = 'evt_test_001'): string
    {
        return json_encode([
            'id'   => $eventId,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id'             => 'cs_test_abc123',
                    'payment_intent' => 'pi_test_xyz456',
                    'amount_total'   => 19900,
                    'metadata'       => [
                        'location_id' => $locationId,
                        'plan_id'     => $planId,
                        'user_id'     => 1,
                    ],
                ],
            ],
        ]);
    }

    public function test_checkout_session_completed_creates_subscription(): void
    {
        Event::fake([SubscriptionActivated::class]);
        $this->mockGateway();

        $company  = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $plan     = SubscriptionPlan::factory()->create([
            'duration_months' => 3,
            'price'           => 199.00,
        ]);

        $payload = $this->makeCheckoutCompletedPayload($location->id, $plan->id);

        $response = $this->postJson('/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => 'valid_signature',
            'Content-Type'     => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('location_subscriptions', [
            'location_id'     => $location->id,
            'plan_id'         => $plan->id,
            'payment_source'  => 'stripe',
            'stripe_session_id' => 'cs_test_abc123',
        ]);

        Event::assertDispatched(SubscriptionActivated::class, function ($event) {
            return $event->source === 'stripe';
        });
    }

    public function test_duplicate_webhook_event_is_skipped(): void
    {
        Event::fake([SubscriptionActivated::class]);
        $this->mockGateway();

        $company  = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $plan     = SubscriptionPlan::factory()->create(['duration_months' => 1]);

        // Pre-insert a log for this event
        StripeWebhookLog::create([
            'stripe_event_id' => 'evt_duplicate_001',
            'event_type'      => 'checkout.session.completed',
            'status'          => 'processed',
            'processed_at'    => now(),
        ]);

        $payload = $this->makeCheckoutCompletedPayload($location->id, $plan->id, 'evt_duplicate_001');

        $response = $this->postJson('/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => 'valid_signature',
        ]);

        $response->assertStatus(200);
        Event::assertNotDispatched(SubscriptionActivated::class);
        $this->assertDatabaseCount('location_subscriptions', 0);
    }

    public function test_invalid_signature_returns_400(): void
    {
        $this->mockGateway();

        $response = $this->postJson('/stripe/webhook', ['id' => 'evt_test'], [
            'Stripe-Signature' => 'invalid',
        ]);

        $response->assertStatus(400);
    }

    public function test_payment_failed_dispatches_event(): void
    {
        Event::fake([SubscriptionPaymentFailed::class]);
        $this->mockGateway();

        $company  = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $plan     = SubscriptionPlan::factory()->create();

        $payload = json_encode([
            'id'   => 'evt_fail_001',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id'       => 'pi_fail_123',
                    'metadata' => [
                        'location_id' => $location->id,
                        'plan_id'     => $plan->id,
                        'user_id'     => 1,
                    ],
                ],
            ],
        ]);

        $response = $this->postJson('/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => 'valid_signature',
        ]);

        $response->assertStatus(200);
        Event::assertDispatched(SubscriptionPaymentFailed::class);
    }
}
