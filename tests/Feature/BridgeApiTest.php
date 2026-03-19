<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Location;
use App\Models\LocationBridge;
use Tests\TestCase;

class BridgeApiTest extends TestCase
{
    // ── Middleware ────────────────────────────────────────────────────────────

    public function test_heartbeat_returns_401_when_no_authorization_header(): void
    {
        $response = $this->postJson('/api/bridges/heartbeat', []);

        $response->assertStatus(401);
    }

    public function test_heartbeat_returns_401_when_api_key_is_invalid(): void
    {
        $response = $this->postJson('/api/bridges/heartbeat', [], [
            'Authorization' => 'Bearer invalid-key',
        ]);

        $response->assertStatus(401);
    }

    public function test_heartbeat_returns_401_when_api_key_is_null_on_bridge(): void
    {
        $bridge = LocationBridge::factory()->create(['api_key' => null]);

        $response = $this->postJson('/api/bridges/heartbeat', [], [
            'Authorization' => 'Bearer ',
        ]);

        $response->assertStatus(401);
    }
}
