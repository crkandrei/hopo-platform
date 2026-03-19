<?php

namespace Tests\Feature;

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

    public function test_heartbeat_returns_401_when_bearer_token_is_empty(): void
    {
        $response = $this->postJson('/api/bridges/heartbeat', [], [
            'Authorization' => 'Bearer ',
        ]);

        $response->assertStatus(401);
    }

    public function test_heartbeat_returns_401_when_key_does_not_match_any_bridge(): void
    {
        // No bridges in DB — any non-empty key should return 401
        $response = $this->postJson('/api/bridges/heartbeat', [], [
            'Authorization' => 'Bearer nonexistent-key-12345',
        ]);

        $response->assertStatus(401);
    }

    // ── Heartbeat ─────────────────────────────────────────────────────────────

    private function bridgeWithKey(): \App\Models\LocationBridge
    {
        return \App\Models\LocationBridge::factory()->create([
            'api_key'   => 'test-api-key-1234',
            'client_id' => null,
        ]);
    }

    public function test_heartbeat_updates_bridge_fields(): void
    {
        $bridge = $this->bridgeWithKey();

        $response = $this->postJson('/api/bridges/heartbeat', [
            'clientId'     => 'abc-uuid',
            'status'       => 'online',
            'version'      => '1.2.0',
            'uptime'       => 3600,
            'bridgeMode'   => 'live',
            'lastPrintAt'  => '2026-03-19T10:00:00Z',
            'printCount'   => 42,
            'zReportCount' => 1,
            'errorCount'   => 3,
        ], ['Authorization' => 'Bearer test-api-key-1234']);

        $response->assertStatus(200);

        $bridge->refresh();
        $this->assertEquals('abc-uuid', $bridge->client_id);
        $this->assertEquals('online', $bridge->status);
        $this->assertEquals('1.2.0', $bridge->version);
        $this->assertEquals(3600, $bridge->uptime);
        $this->assertEquals('live', $bridge->mode);
        $this->assertEquals(42, $bridge->print_count);
        $this->assertEquals(1, $bridge->z_report_count);
        $this->assertEquals(3, $bridge->error_count);
        $this->assertNotNull($bridge->last_seen_at);
        $this->assertNotNull($bridge->last_print_at);
    }

    public function test_heartbeat_does_not_overwrite_existing_client_id(): void
    {
        $bridge = \App\Models\LocationBridge::factory()->create([
            'api_key'   => 'test-key-existing-client',
            'client_id' => 'original-uuid',
        ]);

        $this->postJson('/api/bridges/heartbeat', [
            'clientId' => 'different-uuid',
            'status'   => 'online',
        ], ['Authorization' => 'Bearer test-key-existing-client']);

        $bridge->refresh();
        $this->assertEquals('original-uuid', $bridge->client_id);
    }

    public function test_heartbeat_always_sets_last_seen_at(): void
    {
        $bridge = $this->bridgeWithKey();

        $this->postJson('/api/bridges/heartbeat', [
            'clientId' => 'some-uuid',
            'status'   => 'online',
        ], ['Authorization' => 'Bearer test-api-key-1234']);

        $bridge->refresh();
        $this->assertNotNull($bridge->last_seen_at);
        $this->assertTrue($bridge->last_seen_at->diffInSeconds(now()) < 5);
    }
}
