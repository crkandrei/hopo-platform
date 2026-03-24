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
}
