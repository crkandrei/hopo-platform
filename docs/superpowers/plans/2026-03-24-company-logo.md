# Company Logo Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow each company to upload a custom logo displayed on the public booking page, falling back to the Hopo logo when none is set.

**Architecture:** Add a `logo_path` nullable column to `companies`, store uploads on the `public` disk at `companies/{id}/logo.{ext}`, expose a `logoUrl()` method on the `Company` model, and wire upload/delete into `CompanyController`. The booking layout reads the logo via `$location->company->logoUrl()`.

**Tech Stack:** Laravel (PHP), Blade, Tailwind CSS, Laravel Storage (local public disk), PHPUnit feature tests.

---

## File Map

| Action | File |
|--------|------|
| Create | `database/migrations/2026_03_24_000001_add_logo_path_to_companies_table.php` |
| Modify | `app/Models/Company.php` |
| Modify | `app/Http/Controllers/CompanyController.php` |
| Modify | `routes/web.php` |
| Modify | `resources/views/companies/edit.blade.php` |
| Modify | `resources/views/layouts/booking.blade.php` |
| Modify | `app/Http/Controllers/PublicBookingController.php` |
| Create | `tests/Feature/CompanyLogoTest.php` |

---

## Task 1: Migration + Model — `logo_path` column and `logoUrl()` method

**Files:**
- Create: `database/migrations/2026_03_24_000001_add_logo_path_to_companies_table.php`
- Modify: `app/Models/Company.php`
- Create: `tests/Feature/CompanyLogoTest.php`

- [ ] **Step 1: Write failing tests for `logoUrl()`**

Create `tests/Feature/CompanyLogoTest.php`:

```php
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
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/CompanyLogoTest.php
```

Expected: FAIL — `logo_path` column does not exist yet.

- [ ] **Step 3: Create the migration**

Create `database/migrations/2026_03_24_000001_add_logo_path_to_companies_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};
```

- [ ] **Step 4: Run the migration**

```bash
php artisan migrate
```

Expected: `Migrating: 2026_03_24_000001_add_logo_path_to_companies_table` then `Migrated`.

- [ ] **Step 5: Update `app/Models/Company.php`**

Add `'logo_path'` to `$fillable` and add the `logoUrl()` method. Also add the `Storage` facade import.

In `$fillable` (around line 14), add `'logo_path'` to the array:
```php
protected $fillable = [
    'name',
    'slug',
    'email',
    'phone',
    'logo_path',
    'is_active',
    'daily_report_enabled',
    'daily_report_email',
];
```

Add import at the top of the file (after the existing `use` statements):
```php
use Illuminate\Support\Facades\Storage;
```

Add the `logoUrl()` method after `markDailyReportSent()`:
```php
public function logoUrl(): string
{
    return $this->logo_path
        ? Storage::disk('public')->url($this->logo_path)
        : asset('images/hopo-logo.png');
}
```

- [ ] **Step 6: Run tests to verify they pass**

```bash
php artisan test tests/Feature/CompanyLogoTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_03_24_000001_add_logo_path_to_companies_table.php \
        app/Models/Company.php \
        tests/Feature/CompanyLogoTest.php
git commit -m "feat: add logo_path to companies and logoUrl() accessor"
```

---

## Task 2: Logo upload in `CompanyController::update()`

**Files:**
- Modify: `app/Http/Controllers/CompanyController.php`
- Modify: `tests/Feature/CompanyLogoTest.php`

- [ ] **Step 1: Write failing tests for logo upload**

Append to `tests/Feature/CompanyLogoTest.php`:

```php
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
        $oldPath    = 'companies/99/logo.png';
        Storage::disk('public')->put($oldPath, 'old-image');
        $company    = Company::factory()->create(['logo_path' => $oldPath]);
        $newFile    = \Illuminate\Http\UploadedFile::fake()->image('logo.jpg', 100, 100);

        $this->actingAs($admin)
            ->put(route('companies.update', $company), [
                'name'   => $company->name,
                'logo'   => $newFile,
            ]);

        Storage::disk('public')->assertMissing($oldPath);
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
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
php artisan test tests/Feature/CompanyLogoTest.php --filter=upload
```

Expected: FAILs (no file handling in controller yet).

- [ ] **Step 3: Update `CompanyController::update()`**

Add `Storage` and `RedirectResponse` imports at the top of `app/Http/Controllers/CompanyController.php`:

```php
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
```

Add `'logo'` validation rule to the `$request->validate([...])` block inside `update()` (after `subscription_plan_ids.*`):

```php
'logo' => 'nullable|file|image|mimes:png,jpg,jpeg,webp|max:2048',
```

Add the logo upload block **after** the `$company->update($validated)` call (around line 137) and before the subscription sync:

```php
if ($request->hasFile('logo')) {
    if ($company->logo_path) {
        Storage::disk('public')->delete($company->logo_path);
    }
    $ext  = match ($request->file('logo')->getMimeType()) {
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };
    $path = $request->file('logo')->storeAs(
        "companies/{$company->id}",
        "logo.{$ext}",
        'public'
    );
    $company->logo_path = $path;
    $company->save();
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test tests/Feature/CompanyLogoTest.php
```

Expected: all tests PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/CompanyController.php \
        tests/Feature/CompanyLogoTest.php
git commit -m "feat: add logo upload to CompanyController::update()"
```

---

## Task 3: `deleteLogo()` action + route

**Files:**
- Modify: `app/Http/Controllers/CompanyController.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/CompanyLogoTest.php`

- [ ] **Step 1: Write failing tests for delete logo**

Append to `tests/Feature/CompanyLogoTest.php`:

```php
    // --- delete logo tests ---

    public function test_super_admin_can_delete_company_logo(): void
    {
        Storage::fake('public');
        $admin   = $this->makeSuperAdmin();
        $path    = 'companies/5/logo.png';
        Storage::disk('public')->put($path, 'img');
        $company = Company::factory()->create(['logo_path' => $path]);

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
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
php artisan test tests/Feature/CompanyLogoTest.php --filter=delete
```

Expected: FAIL — route does not exist yet.

- [ ] **Step 3: Add the route to `routes/web.php`**

Find the companies resource line (around line 255). It is inside the `Route::middleware('auth')->group(...)` block — the new route must stay inside that same group. Replace:

```php
    // Companies management (super admin only)
    Route::resource('companies', App\Http\Controllers\CompanyController::class);
```

with:

```php
    // Companies management (super admin only)
    Route::resource('companies', App\Http\Controllers\CompanyController::class);
    Route::delete('/companies/{company}/logo', [App\Http\Controllers\CompanyController::class, 'deleteLogo'])
        ->name('companies.logo.delete');
```

- [ ] **Step 4: Add `deleteLogo()` to `CompanyController`**

Add at the end of `CompanyController`, before the closing `}`:

```php
public function deleteLogo(Company $company): RedirectResponse
{
    $this->authorize('update', $company);

    if ($company->logo_path) {
        Storage::disk('public')->delete($company->logo_path);
        $company->update(['logo_path' => null]);
    }

    return redirect()->back()->with('success', 'Logo șters.');
}
```

- [ ] **Step 5: Run all tests**

```bash
php artisan test tests/Feature/CompanyLogoTest.php
```

Expected: all tests PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/CompanyController.php \
        routes/web.php \
        tests/Feature/CompanyLogoTest.php
git commit -m "feat: add deleteLogo action and route for company logo"
```

---

## Task 4: Admin UI — logo upload in company edit form

**Files:**
- Modify: `resources/views/companies/edit.blade.php`

No automated test for this — the feature tests in Task 2 & 3 cover the HTTP layer. Verify manually after this step.

- [ ] **Step 1: Add `enctype` to the form tag**

In `resources/views/companies/edit.blade.php`, line 25, change:

```blade
<form method="POST" action="{{ route('companies.update', $company) }}">
```

to:

```blade
<form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
```

- [ ] **Step 2: Add the logo section to the form**

Insert the following block **after the Phone field block** (after the closing `</div>` of the Phone section, before the `<!-- Is Active -->` comment, around line 75):

```blade
<!-- Logo -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Logo Companie
    </label>

    @if($company->logo_path)
        <div class="mb-3 flex items-center gap-4">
            <img src="{{ $company->logoUrl() }}" alt="Logo actual" class="h-14 w-auto rounded border border-gray-200 p-1">
            <form method="POST" action="{{ route('companies.logo.delete', $company) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Ești sigur că vrei să ștergi logo-ul?')"
                        class="text-sm text-red-600 hover:text-red-800 underline">
                    Șterge logo
                </button>
            </form>
        </div>
    @endif

    <input type="file"
           name="logo"
           id="logo"
           accept="image/png,image/jpeg,image/webp"
           class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
    <p class="mt-1 text-xs text-gray-500">PNG, JPG sau WebP, max 2 MB. Lasă gol pentru a păstra logo-ul existent.</p>
    @error('logo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

- [ ] **Step 3: Manual smoke test**

```bash
php artisan serve
```

1. Log in as super admin
2. Go to a company's edit page
3. Verify logo file input appears
4. Upload a PNG — confirm it appears as a preview on reload
5. Click "Șterge logo" — confirm the preview disappears and Hopo fallback is shown in booking

- [ ] **Step 4: Commit**

```bash
git add resources/views/companies/edit.blade.php
git commit -m "feat: add logo upload UI to company edit form"
```

---

## Task 5: Booking layout — show company logo

**Files:**
- Modify: `resources/views/layouts/booking.blade.php`
- Modify: `app/Http/Controllers/PublicBookingController.php`
- Modify: `tests/Feature/CompanyLogoTest.php`

- [ ] **Step 1: Write failing tests for booking layout logo**

Append to `tests/Feature/CompanyLogoTest.php`:

```php
    // --- booking page logo tests ---

    public function test_booking_page_shows_company_logo_when_set(): void
    {
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
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
php artisan test tests/Feature/CompanyLogoTest.php --filter=booking
```

Expected: FAIL — booking layout still shows hardcoded Hopo logo.

- [ ] **Step 3: Update `PublicBookingController` to eager-load company**

In `app/Http/Controllers/PublicBookingController.php`, add `$location->loadMissing('company');` as the **first line** of both `showForm()` and `confirmation()`.

`showForm()` (line 19):
```php
public function showForm(Location $location)
{
    $location->loadMissing('company');
    // ... rest unchanged
```

`confirmation()` (line 341):
```php
public function confirmation(Location $location, Request $request)
{
    $location->loadMissing('company');
    // ... rest unchanged
```

- [ ] **Step 4: Update the booking layout**

In `resources/views/layouts/booking.blade.php`, replace lines 15–17:

```blade
            <a href="/">
                <img src="{{ asset('images/hopo-logo.png') }}" alt="Hopo" class="h-12 w-auto">
            </a>
```

with:

```blade
            <img src="{{ $location->company->logoUrl() }}" alt="{{ $location->company->name }}" class="h-12 w-auto">
```

- [ ] **Step 5: Run all tests**

```bash
php artisan test tests/Feature/CompanyLogoTest.php
```

Expected: all tests PASS.

- [ ] **Step 6: Run the full test suite to catch regressions**

```bash
php artisan test
```

Expected: all tests PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/views/layouts/booking.blade.php \
        app/Http/Controllers/PublicBookingController.php \
        tests/Feature/CompanyLogoTest.php
git commit -m "feat: display company logo on booking page with Hopo fallback"
```

---

## Done

After Task 5, the feature is complete:
- Companies can have a logo uploaded from the admin edit page
- The booking page shows the company logo (or Hopo fallback)
- Old files are cleaned up on re-upload or explicit delete
- All logic is covered by feature tests
