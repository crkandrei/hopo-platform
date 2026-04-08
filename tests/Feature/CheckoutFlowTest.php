<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Company;
use App\Models\Location;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    private function makeCompanyAdmin(): User
    {
        $role    = Role::where('name', 'COMPANY_ADMIN')->first();
        $company = Company::factory()->create();

        return User::factory()->create([
            'role_id'    => $role->id,
            'company_id' => $company->id,
            'status'     => 'active',
        ]);
    }

    private function makeSuperAdmin(): User
    {
        $role = Role::where('name', 'SUPER_ADMIN')->first();

        return User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);
    }

    private function makeLocation(User $companyAdmin): Location
    {
        return Location::factory()->create([
            'company_id' => $companyAdmin->company_id,
        ]);
    }

    public function test_company_admin_can_view_checkout_plans(): void
    {
        $user     = $this->makeCompanyAdmin();
        $location = $this->makeLocation($user);
        $plan     = SubscriptionPlan::factory()->create(['is_active' => true]);

        // Set location context in session/container
        $this->app->instance('current.location', $location);

        $response = $this->actingAs($user)->get('/checkout/plans');

        $response->assertStatus(200);
        $response->assertSee($plan->name);
    }

    public function test_company_admin_can_initiate_checkout_session(): void
    {
        $user     = $this->makeCompanyAdmin();
        $location = $this->makeLocation($user);
        $plan     = SubscriptionPlan::factory()->create([
            'is_active'       => true,
            'stripe_price_id' => 'price_test_123',
        ]);

        $this->app->instance('current.location', $location);

        // Mock the gateway to return a test URL
        $this->app->bind(PaymentGatewayInterface::class, function () {
            $mock = $this->createMock(PaymentGatewayInterface::class);
            $mock->method('createCheckoutSession')->willReturn('https://checkout.stripe.com/test_session');
            return $mock;
        });

        $response = $this->actingAs($user)->post('/checkout/session', [
            'plan_id' => $plan->id,
        ]);

        $response->assertRedirect('https://checkout.stripe.com/test_session');
    }

    public function test_super_admin_cannot_access_checkout(): void
    {
        $user = $this->makeSuperAdmin();

        $response = $this->actingAs($user)->get('/checkout/plans');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/checkout/plans');

        $response->assertRedirect('/login');
    }

    public function test_payment_success_page_accessible_to_authenticated_user(): void
    {
        $user     = $this->makeCompanyAdmin();
        $location = $this->makeLocation($user);

        $this->app->instance('current.location', $location);

        $response = $this->actingAs($user)->get('/payment/success?session_id=cs_test_abc123');

        $response->assertStatus(200);
    }
}
