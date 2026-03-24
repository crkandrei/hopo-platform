# Company Logo Feature — Design Spec

**Date:** 2026-03-24
**Status:** Approved

---

## Overview

Allow each company to upload a custom logo that is displayed on the public booking pages instead of the default Hopo logo. If a company has no logo set, the Hopo logo is shown as fallback.

---

## Requirements

- Logo is stored at the **company level** (one logo per company, shared across all its locations)
- Accepted formats: PNG, JPG/JPEG, SVG, WebP
- Max file size: 2MB
- If no company logo is set, fall back to the existing Hopo logo (`/images/hopo-logo.png`)
- When a new logo is uploaded, the old file is deleted from storage
- Admin can explicitly delete a company logo (reverts to Hopo fallback)

---

## Database

**Migration:** Add nullable `logo_path` column to the `companies` table.

```php
$table->string('logo_path')->nullable()->after('phone');
```

**Model:** Add `logo_path` to `Company::$fillable`.

---

## File Storage

- Disk: `public` (`storage/app/public/`)
- Path: `companies/{company_id}/logo.{ext}`
- Accessible via: `/storage/companies/{id}/logo.{ext}` (existing symlink)
- On re-upload: `Storage::disk('public')->delete($company->logo_path)` before saving new file
- On delete: same deletion + set `logo_path = null`

---

## Admin UI — Company Edit Page

On the existing company edit form:

1. **Logo preview** — if `logo_path` is set, show a small preview of the current logo
2. **File input** — `<input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp">`
3. **Delete logo button** — visible only when a logo exists; sends `DELETE /companies/{company}/logo`; reverts to Hopo fallback

---

## Controller & Routes

### `CompanyController::update()`

Add to existing update logic:

```php
if ($request->hasFile('logo')) {
    if ($company->logo_path) {
        Storage::disk('public')->delete($company->logo_path);
    }
    $path = $request->file('logo')->store("companies/{$company->id}", 'public');
    $company->logo_path = $path;
}
```

Validation rule added to existing rules:
```php
'logo' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
```

### New: `CompanyController::deleteLogo()`

```php
public function deleteLogo(Company $company): RedirectResponse
{
    if ($company->logo_path) {
        Storage::disk('public')->delete($company->logo_path);
        $company->update(['logo_path' => null]);
    }
    return redirect()->back()->with('success', 'Logo sters.');
}
```

### New Route

```php
Route::delete('/companies/{company}/logo', [CompanyController::class, 'deleteLogo'])
    ->name('companies.logo.delete');
```

---

## Booking Layout

**File:** `resources/views/layouts/booking.blade.php`

Replace the hardcoded Hopo logo with:

```blade
@if($location->company->logo_path)
    <img src="{{ Storage::url($location->company->logo_path) }}" alt="{{ $location->company->name }}" class="h-12 w-auto">
@else
    <img src="{{ asset('images/hopo-logo.png') }}" alt="Hopo" class="h-12 w-auto">
@endif
```

`$location` is already passed to the booking views by `PublicBookingController`, and `$location->company` is accessible via the existing `belongsTo` relationship on the `Location` model.

---

## Eager Loading

To avoid N+1, ensure the company relationship is eager-loaded in `PublicBookingController`:

```php
$location->loadMissing('company');
```

---

## Out of Scope

- Per-location logo override
- Automatic storage cleanup on company deletion
- Image resizing or optimization
- CDN / S3 integration (uses local public disk; S3 already configured in `filesystems.php` for future use)
