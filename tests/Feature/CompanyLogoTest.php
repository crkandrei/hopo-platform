<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_logo_url_returns_hopo_fallback_when_no_logo_set(): void
    {
        $company = Company::factory()->create(['logo_path' => null]);

        $this->assertStringContainsString('hopo-logo.png', $company->logoUrl());
    }

    public function test_logo_url_returns_storage_url_when_logo_is_set(): void
    {
        Storage::fake('public');
        $company = Company::factory()->create(['logo_path' => null]);
        Storage::disk('public')->put("companies/{$company->id}/logo.png", 'fake-image');
        $company->update(['logo_path' => "companies/{$company->id}/logo.png"]);

        $url = $company->logoUrl();
        $this->assertStringContainsString("companies/{$company->id}/logo.png", $url);
    }

    // --- upload tests ---

    private function makeSuperAdmin(): \App\Models\User
    {
        $role = \App\Models\Role::where('name', 'SUPER_ADMIN')->first();
        return \App\Models\User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);
    }

    public function test_super_admin_can_upload_logo_for_company(): void
    {
        Storage::fake('public');
        $admin   = $this->makeSuperAdmin();
        $company = Company::factory()->create(['logo_path' => null]);
        $file    = \Illuminate\Http\UploadedFile::fake()->image('logo.png');

        $this->actingAs($admin)
            ->put(route('companies.update', $company), [
                'name'   => $company->name,
                'logo'   => $file,
            ])
            ->assertRedirect(route('companies.index'));

        $company->refresh();
        $this->assertNotNull($company->logo_path);
        Storage::disk('public')->assertExists($company->logo_path);
    }

    public function test_uploading_new_logo_deletes_old_one(): void
    {
        Storage::fake('public');
        $admin      = $this->makeSuperAdmin();
        $company    = Company::factory()->create();
        $oldPath    = "companies/{$company->id}/logo.png";
        Storage::disk('public')->put($oldPath, 'old-image');
        $company->update(['logo_path' => $oldPath]);
        $newFile    = \Illuminate\Http\UploadedFile::fake()->image('logo.jpg', 100, 100);

        $this->actingAs($admin)
            ->put(route('companies.update', $company), [
                'name'   => $company->name,
                'logo'   => $newFile,
            ]);

        Storage::disk('public')->assertMissing($oldPath);
        $company->refresh();
        Storage::disk('public')->assertExists($company->logo_path);
    }

    public function test_logo_validation_rejects_oversized_file(): void
    {
        Storage::fake('public');
        $admin   = $this->makeSuperAdmin();
        $company = Company::factory()->create();
        // Create a fake file slightly over 2048 KB
        $file    = \Illuminate\Http\UploadedFile::fake()->image('logo.png')->size(2049);

        $this->actingAs($admin)
            ->put(route('companies.update', $company), [
                'name' => $company->name,
                'logo' => $file,
            ])
            ->assertSessionHasErrors('logo');
    }

    // --- delete logo tests ---

    public function test_super_admin_can_delete_company_logo(): void
    {
        Storage::fake('public');
        $admin   = $this->makeSuperAdmin();
        $company = Company::factory()->create(['logo_path' => null]);
        $path    = "companies/{$company->id}/logo.png";
        Storage::disk('public')->put($path, 'img');
        $company->update(['logo_path' => $path]);

        $this->actingAs($admin)
            ->delete(route('companies.logo.delete', $company))
            ->assertRedirect();

        $company->refresh();
        $this->assertNull($company->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_delete_logo_is_a_no_op_when_no_logo_set(): void
    {
        $admin   = $this->makeSuperAdmin();
        $company = Company::factory()->create(['logo_path' => null]);

        $this->actingAs($admin)
            ->delete(route('companies.logo.delete', $company))
            ->assertRedirect();

        $company->refresh();
        $this->assertNull($company->logo_path);
    }

    public function test_non_admin_cannot_delete_logo(): void
    {
        // CheckLocationSubscription redirects non-super-admins without a bound location
        // before the policy is reached; disable it so we can assert on the 403 from the policy.
        $this->withoutMiddleware(\App\Http\Middleware\CheckLocationSubscription::class);

        $role    = \App\Models\Role::where('name', 'COMPANY_ADMIN')->first();
        $company = Company::factory()->create();
        $user    = \App\Models\User::factory()->create([
            'role_id'    => $role->id,
            'company_id' => $company->id,
            'status'     => 'active',
        ]);

        $this->actingAs($user)
            ->delete(route('companies.logo.delete', $company))
            ->assertForbidden();
    }

    // --- booking page logo tests ---

    public function test_booking_page_shows_company_logo_when_set(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        $company  = Company::factory()->create();
        Storage::disk('public')->put("companies/{$company->id}/logo.png", 'img');
        $company->update(['logo_path' => "companies/{$company->id}/logo.png"]);

        $location = \App\Models\Location::factory()->create(['company_id' => $company->id]);
        $this->setUpBookingLocation($location);

        $response = $this->get(route('booking.show', $location));
        $response->assertStatus(200);
        $response->assertSee("companies/{$company->id}/logo.png");
        $response->assertDontSee('hopo-logo.png');
    }

    public function test_booking_page_shows_hopo_fallback_when_no_logo(): void
    {
        $this->withoutVite();
        $company  = Company::factory()->create(['logo_path' => null]);
        $location = \App\Models\Location::factory()->create(['company_id' => $company->id]);
        $this->setUpBookingLocation($location);

        $response = $this->get(route('booking.show', $location));
        $response->assertStatus(200);
        $response->assertSee('hopo-logo.png');
    }

    /**
     * Set up a location with the minimum required birthday configuration
     * so the booking form does not return 404.
     *
     * Note: BirthdayHall and BirthdayPackage do not have factories — use
     * direct create() calls with the required non-nullable fields.
     * BirthdayHall required: location_id, name, capacity (booking_mode defaults to 'slots').
     * BirthdayPackage required: location_id, name, duration_minutes.
     */
    private function setUpBookingLocation(\App\Models\Location $location): void
    {
        \App\Models\BirthdayHall::create([
            'location_id' => $location->id,
            'name'        => 'Test Hall',
            'capacity'    => 20,
            'is_active'   => true,
        ]);
        \App\Models\BirthdayPackage::create([
            'location_id'      => $location->id,
            'name'             => 'Test Package',
            'duration_minutes' => 120,
            'is_active'        => true,
        ]);
    }
}
