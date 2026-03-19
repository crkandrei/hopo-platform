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
}
