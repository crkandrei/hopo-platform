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

class TokenInvalidationTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;
    private User $staff;
    private Guardian $guardian;
    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\CheckLocationSubscription::class);
        $company = Company::factory()->create();
        $this->location = Location::factory()->create([
            'company_id' => $company->id,
            'bracelet_required' => true,
        ]);
        $this->staff = User::factory()->create([
            'location_id' => $this->location->id,
            'role_id' => Role::where('name', 'STAFF')->first()->id,
        ]);
        $this->guardian = Guardian::factory()->create(['location_id' => $this->location->id]);
        $this->child = Child::factory()->create([
            'location_id' => $this->location->id,
            'guardian_id' => $this->guardian->id,
        ]);
    }

    public function test_token_marked_used_when_bracelet_assigned(): void
    {
        $token = PreCheckinToken::factory()->create([
            'location_id' => $this->location->id,
            'child_id' => $this->child->id,
            'guardian_id' => $this->guardian->id,
        ]);

        $this->actingAs($this->staff)->postJson('/scan-api/assign', [
            'child_id' => $this->child->id,
            'bracelet_code' => 'ABCD1234567',
            'pre_checkin_token' => $token->token,
        ]);

        $token->refresh();
        $this->assertEquals('used', $token->status);
        $this->assertNotNull($token->used_at);
    }

    public function test_assign_succeeds_even_without_token(): void
    {
        $response = $this->actingAs($this->staff)->postJson('/scan-api/assign', [
            'child_id' => $this->child->id,
            'bracelet_code' => 'ABCD1234567',
        ]);

        $response->assertStatus(200);
    }
}
