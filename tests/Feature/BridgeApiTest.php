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

    // ── Logs ──────────────────────────────────────────────────────────────────

    public function test_logs_inserts_entries_into_bridge_logs(): void
    {
        $bridge = \App\Models\LocationBridge::factory()->create(['api_key' => 'logs-test-key']);

        $response = $this->postJson('/api/bridges/logs', [
            'clientId' => 'some-uuid',
            'logs'     => [
                ['level' => 'info',  'message' => 'Print ok',    'timestamp' => '2026-03-19T10:00:00Z'],
                ['level' => 'error', 'message' => 'ECR timeout', 'timestamp' => '2026-03-19T10:01:00Z'],
            ],
        ], ['Authorization' => 'Bearer logs-test-key']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('bridge_logs', [
            'location_id' => $bridge->location_id,
            'level'       => 'info',
            'message'     => 'Print ok',
        ]);
        $this->assertDatabaseHas('bridge_logs', [
            'location_id' => $bridge->location_id,
            'level'       => 'error',
            'message'     => 'ECR timeout',
        ]);
    }

    public function test_logs_returns_422_when_logs_array_is_missing(): void
    {
        \App\Models\LocationBridge::factory()->create(['api_key' => 'logs-validation-key']);

        $response = $this->postJson('/api/bridges/logs', [
            'clientId' => 'uuid',
        ], ['Authorization' => 'Bearer logs-validation-key']);

        $response->assertStatus(422);
    }

    public function test_logs_returns_422_when_log_level_is_invalid(): void
    {
        \App\Models\LocationBridge::factory()->create(['api_key' => 'logs-level-key']);

        $response = $this->postJson('/api/bridges/logs', [
            'clientId' => 'uuid',
            'logs'     => [
                ['level' => 'debug', 'message' => 'test', 'timestamp' => '2026-03-19T10:00:00Z'],
            ],
        ], ['Authorization' => 'Bearer logs-level-key']);

        $response->assertStatus(422);
    }

    // ── Poll Commands ─────────────────────────────────────────────────────────

    public function test_poll_commands_returns_204_when_no_pending_commands(): void
    {
        $bridge = \App\Models\LocationBridge::factory()->create([
            'api_key'   => 'poll-no-cmd-key',
            'client_id' => 'client-uuid-1',
        ]);

        $response = $this->getJson(
            '/api/bridges/commands/client-uuid-1',
            ['Authorization' => 'Bearer poll-no-cmd-key']
        );

        $response->assertStatus(204);
    }

    public function test_poll_commands_returns_command_and_marks_it_sent(): void
    {
        $bridge = \App\Models\LocationBridge::factory()->create([
            'api_key'   => 'poll-cmd-key',
            'client_id' => 'client-uuid-2',
        ]);

        $command = \App\Models\BridgeCommand::create([
            'location_id' => $bridge->location_id,
            'command'     => 'restart',
            'payload'     => null,
            'status'      => 'pending',
        ]);

        $response = $this->getJson(
            '/api/bridges/commands/client-uuid-2',
            ['Authorization' => 'Bearer poll-cmd-key']
        );

        $response->assertStatus(200);
        $response->assertJson([
            'commandId' => $command->id,
            'command'   => 'restart',
            'payload'   => null,
        ]);

        $this->assertDatabaseHas('bridge_commands', [
            'id'     => $command->id,
            'status' => 'sent',
        ]);
    }

    public function test_poll_commands_returns_403_when_client_id_mismatch(): void
    {
        \App\Models\LocationBridge::factory()->create([
            'api_key'   => 'poll-mismatch-key',
            'client_id' => 'real-client-uuid',
        ]);

        $response = $this->getJson(
            '/api/bridges/commands/wrong-client-uuid',
            ['Authorization' => 'Bearer poll-mismatch-key']
        );

        $response->assertStatus(403);
    }

    // ── Ack Command ───────────────────────────────────────────────────────────

    public function test_ack_marks_command_completed_on_success(): void
    {
        $bridge = \App\Models\LocationBridge::factory()->create([
            'api_key'   => 'ack-success-key',
            'client_id' => 'client-uuid-ack',
        ]);

        $command = \App\Models\BridgeCommand::create([
            'location_id' => $bridge->location_id,
            'command'     => 'restart',
            'status'      => 'sent',
        ]);

        $response = $this->postJson(
            '/api/bridges/commands/client-uuid-ack/ack',
            ['commandId' => $command->id, 'success' => true, 'message' => 'Restarting...'],
            ['Authorization' => 'Bearer ack-success-key']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('bridge_commands', [
            'id'          => $command->id,
            'status'      => 'completed',
            'ack_message' => 'Restarting...',
        ]);
    }

    public function test_ack_marks_command_failed_on_failure(): void
    {
        $bridge = \App\Models\LocationBridge::factory()->create([
            'api_key'   => 'ack-fail-key',
            'client_id' => 'client-uuid-ack2',
        ]);

        $command = \App\Models\BridgeCommand::create([
            'location_id' => $bridge->location_id,
            'command'     => 'restart',
            'status'      => 'sent',
        ]);

        $this->postJson(
            '/api/bridges/commands/client-uuid-ack2/ack',
            ['commandId' => $command->id, 'success' => false, 'message' => 'Failed to restart'],
            ['Authorization' => 'Bearer ack-fail-key']
        );

        $this->assertDatabaseHas('bridge_commands', [
            'id'     => $command->id,
            'status' => 'failed',
        ]);
    }

    public function test_ack_returns_404_when_command_belongs_to_different_location(): void
    {
        $bridge1 = \App\Models\LocationBridge::factory()->create(['api_key' => 'ack-loc1-key', 'client_id' => 'c1']);
        $bridge2 = \App\Models\LocationBridge::factory()->create(['api_key' => 'ack-loc2-key', 'client_id' => 'c2']);

        $commandForBridge2 = \App\Models\BridgeCommand::create([
            'location_id' => $bridge2->location_id,
            'command'     => 'restart',
            'status'      => 'sent',
        ]);

        $response = $this->postJson(
            '/api/bridges/commands/c1/ack',
            ['commandId' => $commandForBridge2->id, 'success' => true, 'message' => 'ok'],
            ['Authorization' => 'Bearer ack-loc1-key']
        );

        $response->assertStatus(404);
    }
}
