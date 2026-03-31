<?php

namespace Tests\Feature\PreCheckin;

use App\Models\Child;
use App\Models\Company;
use App\Models\Guardian;
use App\Models\Location;
use App\Models\PreCheckinToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExistingClientPreCheckinTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $company = Company::factory()->create();
        $this->location = Location::factory()->create([
            'company_id' => $company->id,
            'pre_checkin_enabled' => true,
        ]);
    }

    public function test_existing_client_finds_guardian_by_phone(): void
    {
        $guardian = Guardian::factory()->create([
            'location_id' => $this->location->id,
            'phone' => '0722333444',
        ]);
        $child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $guardian->id,
        ]);

        $response = $this->post(route('pre-checkin.submit-existing', $this->location), [
            'guardian_phone' => '0722333444',
            'website' => '',
        ]);

        $response->assertStatus(200);
        $response->assertSee($child->name);
    }

    public function test_existing_client_shows_error_if_phone_not_found(): void
    {
        $response = $this->post(route('pre-checkin.submit-existing', $this->location), [
            'guardian_phone' => '0700000000',
            'website' => '',
        ]);

        $response->assertSessionHasErrors('guardian_phone');
    }

    public function test_existing_client_shows_no_children_message(): void
    {
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'phone' => '0722333444',
        ]);

        $response = $this->post(route('pre-checkin.submit-existing', $this->location), [
            'guardian_phone' => '0722333444',
            'website' => '',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Nu ai copii înregistrați');
    }

    public function test_existing_client_shows_terms_if_expired(): void
    {
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'phone' => '0722333444',
            'terms_accepted_at' => null,
        ]);

        $response = $this->post(route('pre-checkin.submit-existing', $this->location), [
            'guardian_phone' => '0722333444',
            'website' => '',
        ]);

        $response->assertSessionHasErrors('terms_accept');
    }

    public function test_generate_token_for_existing_child(): void
    {
        $guardian = Guardian::factory()->create([
            'location_id' => $this->location->id,
            'phone' => '0722333444',
        ]);
        $child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $guardian->id,
        ]);

        $response = $this->post(route('pre-checkin.generate-token', $this->location), [
            'guardian_phone' => '0722333444',
            'child_id' => $child->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);

        $this->assertDatabaseCount('pre_checkin_tokens', 1);
        $token = PreCheckinToken::first();
        $this->assertEquals($child->id, $token->child_id);
        $this->assertEquals('pending', $token->status);
    }

    public function test_generate_token_rejects_child_from_different_guardian(): void
    {
        $guardian = Guardian::factory()->create([
            'location_id' => $this->location->id,
            'phone' => '0722333444',
        ]);
        $otherGuardian = Guardian::factory()->create(['location_id' => $this->location->id]);
        $child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $otherGuardian->id,
        ]);

        $response = $this->post(route('pre-checkin.generate-token', $this->location), [
            'guardian_phone' => '0722333444',
            'child_id' => $child->id,
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseCount('pre_checkin_tokens', 0);
    }

    public function test_generate_token_cancels_previous_pending_token(): void
    {
        $guardian = Guardian::factory()->create([
            'location_id' => $this->location->id,
            'phone' => '0722333444',
        ]);
        $child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $guardian->id,
        ]);
        $oldToken = PreCheckinToken::factory()->create([
            'location_id' => $this->location->id,
            'child_id' => $child->id,
            'guardian_id' => $guardian->id,
            'status' => 'pending',
        ]);

        $this->post(route('pre-checkin.generate-token', $this->location), [
            'guardian_phone' => '0722333444',
            'child_id' => $child->id,
        ]);

        $oldToken->refresh();
        $this->assertEquals('used', $oldToken->status);
        $this->assertDatabaseCount('pre_checkin_tokens', 2);
    }
}
