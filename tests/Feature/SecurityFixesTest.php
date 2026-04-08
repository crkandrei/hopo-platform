<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityFixesTest extends TestCase
{
    private int $obLevel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->obLevel) {
            ob_end_clean();
        }
        parent::tearDown();
    }

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

    // ── Fix 5: Rate limiting ──────────────────────────────────────────────

    public function test_login_is_throttled_after_10_attempts(): void
    {
        // Bypass CSRF so each request reaches the throttle middleware.
        // POST /login is not CSRF-exempt, so without this the test would
        // get 419 on every request before accumulating throttle hits.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', ['username' => 'x', 'password' => 'y']);
        }
        $response = $this->post('/login', ['username' => 'x', 'password' => 'y']);
        $response->assertStatus(429);
    }

    public function test_birthday_reservation_action_is_throttled(): void
    {
        // GET requests are not subject to CSRF — no withoutMiddleware needed.
        $token = str_repeat('a', 8) . '-' . str_repeat('b', 4) . '-' . str_repeat('c', 4) . '-' . str_repeat('d', 4) . '-' . str_repeat('e', 12);
        for ($i = 0; $i < 20; $i++) {
            $this->get("/rezervari/{$token}/confirm");
        }
        $response = $this->get("/rezervari/{$token}/confirm");
        $response->assertStatus(429);
    }
}
