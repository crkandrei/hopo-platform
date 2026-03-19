<?php

namespace Tests\Feature;

use App\Models\BridgeCommand;
use App\Models\Company;
use App\Models\Location;
use App\Models\LocationBridge;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class LocationBridgeAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\CheckLocationSubscription::class,
        ]);
    }

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', 'SUPER_ADMIN')->first()->id,
            'status'  => 'active',
        ]);
    }

    private function makeCompanyAdminWithLocation(): array
    {
        $role     = Role::where('name', 'COMPANY_ADMIN')->first();
        $company  = Company::factory()->create();
        $admin    = User::factory()->create(['role_id' => $role->id, 'company_id' => $company->id, 'status' => 'active']);
        $location = Location::factory()->create(['company_id' => $company->id]);
        return [$admin, $location];
    }

    // ── Generate API Key ──────────────────────────────────────────────────────

    public function test_super_admin_can_generate_api_key_for_location(): void
    {
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/generate-key");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('location_bridges', [
            'location_id' => $location->id,
        ]);

        $bridge = LocationBridge::where('location_id', $location->id)->first();
        $this->assertNotNull($bridge->api_key);
        $this->assertEquals(64, strlen($bridge->api_key));
    }

    public function test_company_admin_can_generate_api_key_for_own_location(): void
    {
        [$admin, $location] = $this->makeCompanyAdminWithLocation();

        $response = $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/generate-key");

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_company_admin_cannot_generate_api_key_for_other_company_location(): void
    {
        [$admin, ] = $this->makeCompanyAdminWithLocation();
        $otherLocation = Location::factory()->create(); // different company

        $response = $this->actingAs($admin)
            ->post("/locations/{$otherLocation->slug}/bridge/generate-key");

        $response->assertStatus(403);
    }

    public function test_generate_key_overwrites_existing_key(): void
    {
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create();
        $bridge   = LocationBridge::factory()->create([
            'location_id' => $location->id,
            'api_key'     => 'old-key-value',
        ]);

        $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/generate-key");

        $bridge->refresh();
        $this->assertNotEquals('old-key-value', $bridge->api_key);
    }

    // ── Create Command ────────────────────────────────────────────────────────

    public function test_super_admin_can_create_restart_command(): void
    {
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create();
        LocationBridge::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/commands", [
                'command' => 'restart',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bridge_commands', [
            'location_id' => $location->id,
            'command'     => 'restart',
            'status'      => 'pending',
        ]);
    }

    public function test_can_create_set_config_command_with_payload(): void
    {
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create();
        LocationBridge::factory()->create(['location_id' => $location->id]);

        $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/commands", [
                'command' => 'set_config',
                'payload' => ['BRIDGE_MODE' => 'test'],
            ]);

        $this->assertDatabaseHas('bridge_commands', [
            'location_id' => $location->id,
            'command'     => 'set_config',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_command(): void
    {
        $location = Location::factory()->create();

        $response = $this->post("/locations/{$location->slug}/bridge/commands", [
            'command' => 'restart',
        ]);

        $response->assertRedirect('/login');
    }
}
