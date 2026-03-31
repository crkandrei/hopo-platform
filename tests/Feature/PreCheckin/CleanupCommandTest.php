<?php

namespace Tests\Feature\PreCheckin;

use App\Models\Child;
use App\Models\Company;
use App\Models\Guardian;
use App\Models\Location;
use App\Models\PreCheckinToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupCommandTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $company = Company::factory()->create();
        $this->location = Location::factory()->create(['company_id' => $company->id]);
    }

    private function makeToken(array $attrs = []): PreCheckinToken
    {
        $guardian = Guardian::factory()->create(['location_id' => $this->location->id]);
        $child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $guardian->id,
        ]);

        return PreCheckinToken::factory()->create(array_merge([
            'location_id' => $this->location->id,
            'child_id' => $child->id,
            'guardian_id' => $guardian->id,
        ], $attrs));
    }

    public function test_cleanup_deletes_expired_pending_tokens_older_than_24h(): void
    {
        $old = $this->makeToken([
            'status' => 'pending',
            'expires_at' => now()->subHours(25),
        ]);

        $recent = $this->makeToken([
            'status' => 'pending',
            'expires_at' => now()->subMinutes(30),
        ]);

        $this->artisan('pre-checkin:cleanup')->assertExitCode(0);

        $this->assertDatabaseMissing('pre_checkin_tokens', ['id' => $old->id]);
        $this->assertDatabaseHas('pre_checkin_tokens', ['id' => $recent->id]);
    }

    public function test_cleanup_deletes_used_tokens_older_than_7_days(): void
    {
        $old = $this->makeToken([
            'status' => 'used',
            'used_at' => now()->subDays(8),
        ]);

        $recent = $this->makeToken([
            'status' => 'used',
            'used_at' => now()->subDay(),
        ]);

        $this->artisan('pre-checkin:cleanup')->assertExitCode(0);

        $this->assertDatabaseMissing('pre_checkin_tokens', ['id' => $old->id]);
        $this->assertDatabaseHas('pre_checkin_tokens', ['id' => $recent->id]);
    }

    public function test_cleanup_keeps_valid_pending_tokens(): void
    {
        $valid = $this->makeToken([
            'status' => 'pending',
            'expires_at' => now()->addMinutes(45),
        ]);

        $this->artisan('pre-checkin:cleanup')->assertExitCode(0);

        $this->assertDatabaseHas('pre_checkin_tokens', ['id' => $valid->id]);
    }
}
