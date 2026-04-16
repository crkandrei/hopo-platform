<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Guardian;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GdprComplianceReportTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Location $location;
    private User $companyAdmin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->location = Location::factory()->create(['company_id' => $this->company->id]);

        $this->companyAdmin = User::factory()->create([
            'company_id' => $this->company->id,
            'location_id' => $this->location->id,
            'role_id' => Role::where('name', 'COMPANY_ADMIN')->first()->id,
        ]);

        $this->staff = User::factory()->create([
            'location_id' => $this->location->id,
            'role_id' => Role::where('name', 'STAFF')->first()->id,
        ]);

        $this->app->instance('current.location', $this->location);
        $this->withoutMiddleware(\App\Http\Middleware\CheckLocationSubscription::class);
    }

    #[Test]
    public function company_admin_can_access_gdpr_compliance_page(): void
    {
        $response = $this->actingAs($this->companyAdmin)
            ->get(route('reports.gdpr-compliance'));

        $response->assertStatus(200);
    }

    #[Test]
    public function staff_cannot_access_gdpr_compliance_page(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('reports.gdpr-compliance'));

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_access_gdpr_compliance_page(): void
    {
        $response = $this->get(route('reports.gdpr-compliance'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function company_admin_can_access_gdpr_compliance_data(): void
    {
        $response = $this->actingAs($this->companyAdmin)
            ->getJson(route('reports.gdpr-compliance.data'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['page', 'per_page', 'total', 'total_pages'],
                'summary' => ['total', 'both_accepted', 'pending'],
            ]);
    }

    #[Test]
    public function staff_cannot_access_gdpr_compliance_data(): void
    {
        $response = $this->actingAs($this->staff)
            ->getJson(route('reports.gdpr-compliance.data'));

        $response->assertStatus(403);
    }

    #[Test]
    public function company_admin_can_access_gdpr_compliance_pdf(): void
    {
        $response = $this->actingAs($this->companyAdmin)
            ->get(route('reports.gdpr-compliance.pdf'));

        $response->assertStatus(200);
    }

    #[Test]
    public function staff_cannot_access_gdpr_compliance_pdf(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('reports.gdpr-compliance.pdf'));

        $response->assertStatus(403);
    }
}
