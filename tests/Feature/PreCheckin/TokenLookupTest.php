<?php

namespace Tests\Feature\PreCheckin;

use App\Models\Child;
use App\Models\Company;
use App\Models\Guardian;
use App\Models\Location;
use App\Models\PreCheckinToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenLookupTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\CheckLocationSubscription::class);
        $company = Company::factory()->create();
        $this->location = Location::factory()->create(['company_id' => $company->id]);
        $this->staff = User::factory()->create([
            'location_id' => $this->location->id,
            'role_id' => Role::where('name', 'STAFF')->first()->id,
        ]);
    }

    public function test_lookup_requires_authentication(): void
    {
        $response = $this->getJson('/scan-api/pre-checkin/some-token');

        $response->assertStatus(401);
    }

    public function test_lookup_returns_child_data_for_valid_token(): void
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

        $response = $this->actingAs($this->staff)->getJson("/scan-api/pre-checkin/{$token->token}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'child' => ['id', 'name'],
                'guardian' => ['id', 'name', 'phone'],
            ])
            ->assertJson([
                'success' => true,
                'child' => ['id' => $child->id, 'name' => $child->name],
            ]);
    }

    public function test_lookup_returns_error_for_expired_token(): void
    {
        $guardian = Guardian::factory()->create(['location_id' => $this->location->id]);
        $child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $guardian->id,
        ]);
        $token = PreCheckinToken::factory()->expired()->create([
            'location_id' => $this->location->id,
            'child_id' => $child->id,
            'guardian_id' => $guardian->id,
        ]);

        $response = $this->actingAs($this->staff)->getJson("/scan-api/pre-checkin/{$token->token}");

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Cod expirat']);
    }

    public function test_lookup_returns_error_for_used_token(): void
    {
        $guardian = Guardian::factory()->create(['location_id' => $this->location->id]);
        $child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $guardian->id,
        ]);
        $token = PreCheckinToken::factory()->used()->create([
            'location_id' => $this->location->id,
            'child_id' => $child->id,
            'guardian_id' => $guardian->id,
        ]);

        $response = $this->actingAs($this->staff)->getJson("/scan-api/pre-checkin/{$token->token}");

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Cod deja folosit']);
    }

    public function test_lookup_rejects_token_from_different_location(): void
    {
        $otherCompany = Company::factory()->create();
        $otherLocation = Location::factory()->create(['company_id' => $otherCompany->id]);
        $guardian = Guardian::factory()->create(['location_id' => $otherLocation->id]);
        $child = Child::factory()->create([
            'location_id' => $otherLocation->id,
            'guardian_id' => $guardian->id,
        ]);
        $token = PreCheckinToken::factory()->create([
            'location_id' => $otherLocation->id,
            'child_id' => $child->id,
            'guardian_id' => $guardian->id,
        ]);

        $response = $this->actingAs($this->staff)->getJson("/scan-api/pre-checkin/{$token->token}");

        $response->assertStatus(422);
    }
}
