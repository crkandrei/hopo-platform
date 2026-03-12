<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Models\Role;
use App\Models\Voucher;
use App\Models\PlaySession;
use App\Models\Child;
use App\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Location $location;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $company = Company::factory()->create();
        $this->location = Location::factory()->create(['company_id' => $company->id]);
        $this->admin = User::factory()->create([
            'company_id' => $company->id,
            'location_id' => $this->location->id,
            'role_id' => Role::where('name', 'COMPANY_ADMIN')->first()->id,
        ]);
    }

    public function test_voucher_index_requires_auth(): void
    {
        $response = $this->get(route('locations.vouchers.index', $this->location));
        $response->assertRedirect(route('login'));
    }

    public function test_voucher_index_accessible_by_company_admin(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('locations.vouchers.index', $this->location));
        $response->assertOk();
    }

    public function test_voucher_create_and_store_generates_unique_code(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('locations.vouchers.store', $this->location), [
            'type' => 'amount',
            'initial_value' => 50,
            'expires_at' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Test voucher',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $voucher = Voucher::withoutGlobalScope('location')->where('location_id', $this->location->id)->first();
        $this->assertNotNull($voucher);
        $this->assertSame(8, strlen($voucher->code));
        $this->assertEqualsWithDelta(50, (float) $voucher->remaining_value, 0.01);
    }

    public function test_voucher_report_page_returns_stats(): void
    {
        Voucher::withoutGlobalScope('location')->create([
            'location_id' => $this->location->id,
            'code' => 'REPORT1',
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 60,
            'is_active' => true,
        ]);
        $this->actingAs($this->admin);
        $response = $this->get(route('locations.vouchers.report', $this->location));
        $response->assertOk();
        $response->assertSee('REPORT1');
    }

    public function test_validate_endpoint_returns_valid_for_existing_voucher(): void
    {
        $voucher = Voucher::withoutGlobalScope('location')->create([
            'location_id' => $this->location->id,
            'code' => 'VALIDCODE',
            'type' => 'amount',
            'initial_value' => 80,
            'remaining_value' => 80,
            'is_active' => true,
        ]);
        $response = $this->postJson(route('vouchers.validate'), [
            'code' => 'VALIDCODE',
            'location_id' => $this->location->id,
        ]);
        $response->assertOk();
        $response->assertJson(['valid' => true]);
        $response->assertJsonPath('voucher_data.code', 'VALIDCODE');
    }
}
