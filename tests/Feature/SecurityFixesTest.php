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

    // ── Fix 2: Blog slug whitelist ────────────────────────────────────────

    public function test_known_blog_slug_returns_200(): void
    {
        $response = $this->get('/blog/bon-fiscal-automat-loc-de-joaca');
        $response->assertStatus(200);
    }

    public function test_unknown_blog_slug_returns_404(): void
    {
        $response = $this->get('/blog/some-nonexistent-article');
        $response->assertStatus(404);
    }

    // ── Fix 4: dashboard-api POST routes must require CSRF ────────────────

    public function test_dashboard_api_and_reports_api_are_not_csrf_exempt(): void
    {
        // Laravel's VerifyCsrfToken bypasses token checks when runningUnitTests()
        // is true, so we cannot verify the 419 response via HTTP in PHPUnit.
        // Instead we assert the middleware configuration directly: dashboard-api/*
        // and reports-api/* must NOT appear in the CSRF except list.
        $middleware = app(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $excludedPaths = $middleware->getExcludedPaths();

        $this->assertNotContains('dashboard-api/*', $excludedPaths,
            'dashboard-api/* must not be exempt from CSRF verification');
        $this->assertNotContains('reports-api/*', $excludedPaths,
            'reports-api/* must not be exempt from CSRF verification');
        $this->assertContains('stripe/webhook', $excludedPaths,
            'stripe/webhook must remain exempt (uses Stripe signature verification)');
    }
}
