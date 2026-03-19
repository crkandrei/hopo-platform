<?php

namespace Tests\Unit;

use App\Models\LocationBridge;
use Tests\TestCase;

class MarkBridgesOfflineTest extends TestCase
{
    public function test_marks_online_bridges_offline_when_last_seen_exceeds_90_seconds(): void
    {
        $stale = LocationBridge::factory()->create([
            'status'       => 'online',
            'last_seen_at' => now()->subSeconds(91),
        ]);

        $fresh = LocationBridge::factory()->create([
            'status'       => 'online',
            'last_seen_at' => now()->subSeconds(30),
        ]);

        $this->artisan('bridges:mark-offline');

        $this->assertEquals('offline', $stale->fresh()->status);
        $this->assertEquals('online', $fresh->fresh()->status);
    }

    public function test_does_not_touch_never_connected_bridges(): void
    {
        $bridge = LocationBridge::factory()->create([
            'status'       => 'never_connected',
            'last_seen_at' => null,
        ]);

        $this->artisan('bridges:mark-offline');

        $this->assertEquals('never_connected', $bridge->fresh()->status);
    }

    public function test_does_not_touch_already_offline_bridges(): void
    {
        $bridge = LocationBridge::factory()->create([
            'status'       => 'offline',
            'last_seen_at' => now()->subMinutes(10),
        ]);

        $this->artisan('bridges:mark-offline');

        $this->assertEquals('offline', $bridge->fresh()->status);
    }
}
