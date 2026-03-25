<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityFixesTest extends TestCase
{
    // ── Fix 1: /api/scan/* routes must not exist ──────────────────────────

    public function test_public_api_scan_generate_does_not_exist(): void
    {
        $response = $this->postJson('/api/scan/generate', []);
        $response->assertStatus(404);
    }

    public function test_public_api_scan_create_child_does_not_exist(): void
    {
        $response = $this->postJson('/api/scan/create-child', []);
        $response->assertStatus(404);
    }

    public function test_public_api_scan_cleanup_does_not_exist(): void
    {
        $response = $this->postJson('/api/scan/cleanup', []);
        $response->assertStatus(404);
    }
}
