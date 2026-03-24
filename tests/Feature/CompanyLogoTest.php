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
}
