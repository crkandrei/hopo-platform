<?php

namespace Tests\Feature\PreCheckin;

use App\Models\Child;
use App\Models\Company;
use App\Models\Guardian;
use App\Models\Location;
use App\Models\PreCheckinToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewClientPreCheckinTest extends TestCase
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

    public function test_index_returns_404_if_pre_checkin_disabled(): void
    {
        $this->location->update(['pre_checkin_enabled' => false]);

        $response = $this->get(route('pre-checkin.index', $this->location));

        $response->assertStatus(404);
    }

    public function test_index_returns_200_if_pre_checkin_enabled(): void
    {
        $response = $this->get(route('pre-checkin.index', $this->location));

        $response->assertStatus(200);
    }

    public function test_new_client_creates_guardian_child_and_token(): void
    {
        $response = $this->post(route('pre-checkin.submit-new', $this->location), [
            'guardian_first_name' => 'Maria',
            'guardian_last_name' => 'Ionescu',
            'guardian_phone' => '0722111222',
            'child_name' => 'Andrei Ionescu',
            'terms_accept' => '1',
            'gdpr_accept' => '1',
            'website' => '',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('guardians', [
            'phone' => '0722111222',
            'name' => 'MARIA IONESCU',
            'location_id' => $this->location->id,
        ]);

        $this->assertDatabaseHas('children', [
            'name' => 'Andrei Ionescu',
            'location_id' => $this->location->id,
        ]);

        $this->assertDatabaseCount('pre_checkin_tokens', 1);

        $token = PreCheckinToken::first();
        $this->assertEquals('pending', $token->status);
        $this->assertTrue($token->expires_at->isFuture());
    }

    public function test_new_client_uses_existing_guardian_if_phone_exists(): void
    {
        $existingGuardian = Guardian::factory()->create([
            'location_id' => $this->location->id,
            'phone' => '0722111222',
        ]);

        $this->post(route('pre-checkin.submit-new', $this->location), [
            'guardian_first_name' => 'Alt',
            'guardian_last_name' => 'Nume',
            'guardian_phone' => '0722111222',
            'child_name' => 'Copil Nou',
            'terms_accept' => '1',
            'gdpr_accept' => '1',
            'website' => '',
        ]);

        $this->assertDatabaseCount('guardians', 1);

        $child = Child::first();
        $this->assertEquals($existingGuardian->id, $child->guardian_id);
    }

    public function test_honeypot_rejects_bots_silently(): void
    {
        $response = $this->post(route('pre-checkin.submit-new', $this->location), [
            'guardian_first_name' => 'Bot',
            'guardian_last_name' => 'Bot',
            'guardian_phone' => '0700000000',
            'child_name' => 'Bot Child',
            'terms_accept' => '1',
            'gdpr_accept' => '1',
            'website' => 'http://spam.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('guardians', 0);
        $this->assertDatabaseCount('pre_checkin_tokens', 0);
    }

    public function test_new_client_requires_terms_acceptance(): void
    {
        $response = $this->post(route('pre-checkin.submit-new', $this->location), [
            'guardian_first_name' => 'Maria',
            'guardian_last_name' => 'Ionescu',
            'guardian_phone' => '0722111222',
            'child_name' => 'Andrei',
            'terms_accept' => '0',
            'gdpr_accept' => '1',
            'website' => '',
        ]);

        $response->assertSessionHasErrors('terms_accept');
        $this->assertDatabaseCount('pre_checkin_tokens', 0);
    }

    public function test_new_client_requires_gdpr_acceptance(): void
    {
        $response = $this->post(route('pre-checkin.submit-new', $this->location), [
            'guardian_first_name' => 'Maria',
            'guardian_last_name' => 'Ionescu',
            'guardian_phone' => '0722111222',
            'child_name' => 'Andrei',
            'terms_accept' => '1',
            'gdpr_accept' => '0',
            'website' => '',
        ]);

        $response->assertSessionHasErrors('gdpr_accept');
        $this->assertDatabaseCount('pre_checkin_tokens', 0);
    }

    public function test_qr_page_shows_for_valid_token(): void
    {
        $guardian = Guardian::factory()->create(['location_id' => $this->location->id]);
        $child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $guardian->id,
        ]);
        $token = PreCheckinToken::factory()->create([
            'location_id' => $this->location->id,
            'child_id' => $child->id,
            'guardian_id' => $guardian->id,
        ]);

        $response = $this->get(route('pre-checkin.qr', [
            'location' => $this->location,
            'token' => $token->token,
        ]));

        $response->assertStatus(200);
        $response->assertSee($token->token);
    }

    public function test_qr_page_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('pre-checkin.qr', [
            'location' => $this->location,
            'token' => 'token-inexistent',
        ]));

        $response->assertStatus(404);
    }
}
