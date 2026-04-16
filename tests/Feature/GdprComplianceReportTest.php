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
            'company_id' => $this->company->id,
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
        Guardian::factory()->create(['location_id' => $this->location->id]);

        $response = $this->actingAs($this->companyAdmin)
            ->getJson(route('reports.gdpr-compliance.data'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    ['id', 'name', 'phone', 'terms_accepted_at', 'terms_version', 'gdpr_accepted_at', 'gdpr_version', 'created_at']
                ],
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

    #[Test]
    public function guest_cannot_access_gdpr_compliance_data(): void
    {
        $response = $this->getJson(route('reports.gdpr-compliance.data'));
        $response->assertStatus(401);
    }

    #[Test]
    public function guest_cannot_access_gdpr_compliance_pdf(): void
    {
        $response = $this->get(route('reports.gdpr-compliance.pdf'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function data_endpoint_returns_only_location_guardians(): void
    {
        $otherLocation = Location::factory()->create(['company_id' => $this->company->id]);

        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'name' => 'Parinte Local',
        ]);
        Guardian::factory()->create([
            'location_id' => $otherLocation->id,
            'name' => 'Parinte Alt',
        ]);

        $response = $this->actingAs($this->companyAdmin)
            ->getJson(route('reports.gdpr-compliance.data'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Parinte Local', $data[0]['name']);
    }

    #[Test]
    public function data_endpoint_filters_by_terms_status_not_accepted(): void
    {
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'name' => 'Fara Termeni',
            'terms_accepted_at' => null,
            'terms_version' => null,
        ]);
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'name' => 'Cu Termeni',
            'terms_accepted_at' => now(),
            'terms_version' => '1.0',
        ]);

        $response = $this->actingAs($this->companyAdmin)
            ->getJson(route('reports.gdpr-compliance.data', ['terms_status' => 'not_accepted']));

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Fara Termeni', $data[0]['name']);
    }

    #[Test]
    public function data_endpoint_filters_by_gdpr_status_accepted(): void
    {
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'name' => 'Cu GDPR',
            'gdpr_accepted_at' => now(),
            'gdpr_version' => '1.0',
        ]);
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'name' => 'Fara GDPR',
            'gdpr_accepted_at' => null,
            'gdpr_version' => null,
        ]);

        $response = $this->actingAs($this->companyAdmin)
            ->getJson(route('reports.gdpr-compliance.data', ['gdpr_status' => 'accepted']));

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Cu GDPR', $data[0]['name']);
    }

    #[Test]
    public function data_endpoint_summary_counts_correctly(): void
    {
        // Both accepted
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'terms_accepted_at' => now(),
            'gdpr_accepted_at' => now(),
        ]);
        // Only terms
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'terms_accepted_at' => now(),
            'gdpr_accepted_at' => null,
        ]);
        // Neither
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'terms_accepted_at' => null,
            'gdpr_accepted_at' => null,
        ]);

        $response = $this->actingAs($this->companyAdmin)
            ->getJson(route('reports.gdpr-compliance.data'));

        $summary = $response->json('summary');
        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(1, $summary['both_accepted']);
        $this->assertEquals(2, $summary['pending']);
    }

    #[Test]
    public function data_endpoint_paginates_correctly(): void
    {
        Guardian::factory()->count(15)->create(['location_id' => $this->location->id]);

        $response = $this->actingAs($this->companyAdmin)
            ->getJson(route('reports.gdpr-compliance.data', ['per_page' => 10, 'page' => 1]));

        $meta = $response->json('meta');
        $this->assertEquals(15, $meta['total']);
        $this->assertEquals(2, $meta['total_pages']);
        $this->assertCount(10, $response->json('data'));
    }

    #[Test]
    public function pdf_endpoint_contains_guardian_name(): void
    {
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'name' => 'Ion Popescu',
            'terms_accepted_at' => now(),
            'gdpr_accepted_at' => now(),
        ]);

        $response = $this->actingAs($this->companyAdmin)
            ->get(route('reports.gdpr-compliance.pdf'));

        $response->assertStatus(200);
        $response->assertSee('Ion Popescu');
    }

    #[Test]
    public function pdf_endpoint_contains_location_name(): void
    {
        $response = $this->actingAs($this->companyAdmin)
            ->get(route('reports.gdpr-compliance.pdf'));

        $response->assertStatus(200);
        $response->assertSee($this->location->name);
    }

    #[Test]
    public function pdf_endpoint_excludes_guardians_not_matching_filter(): void
    {
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'name' => 'Fara Termeni',
            'terms_accepted_at' => null,
        ]);
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'name' => 'Cu Termeni',
            'terms_accepted_at' => now(),
        ]);

        $response = $this->actingAs($this->companyAdmin)
            ->get(route('reports.gdpr-compliance.pdf', ['terms_status' => 'accepted']));

        $response->assertStatus(200);
        $response->assertSee('Cu Termeni');
        $response->assertDontSee('Fara Termeni');
    }

    #[Test]
    public function data_endpoint_summary_is_independent_of_filters(): void
    {
        // 3 guardians total: 2 accepted terms, 1 did not
        Guardian::factory()->count(2)->create([
            'location_id' => $this->location->id,
            'terms_accepted_at' => now(),
            'gdpr_accepted_at' => now(),
        ]);
        Guardian::factory()->create([
            'location_id' => $this->location->id,
            'terms_accepted_at' => null,
            'gdpr_accepted_at' => null,
        ]);

        // Apply filter that only returns 2 results
        $response = $this->actingAs($this->companyAdmin)
            ->getJson(route('reports.gdpr-compliance.data', ['terms_status' => 'accepted']));

        $data = $response->json('data');
        $this->assertCount(2, $data);

        // But summary should still reflect all 3 guardians
        $summary = $response->json('summary');
        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(2, $summary['both_accepted']);
        $this->assertEquals(1, $summary['pending']);
    }
}
