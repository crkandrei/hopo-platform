# Company Logo Feature — Design Spec

**Date:** 2026-03-24
**Status:** Approved

---

## Overview

Allow each company to upload a custom logo that is displayed on the public booking pages instead of the default Hopo logo. If a company has no logo set, the Hopo logo is shown as fallback.

---

## Requirements

- Logo is stored at the **company level** (one logo per company, shared across all its locations)
- Accepted formats: PNG, JPG/JPEG, WebP (SVG excluded — SVG files can embed scripts and represent a stored XSS risk on public-facing pages)
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

> Note: `->after()` is respected in MySQL but silently ignored in SQLite (used in tests). No functional impact.

**Model:** Add `logo_path` to `Company::$fillable`.

**Accessor:** Add a `logoUrl()` method on `Company` to keep storage logic out of views and make it reusable (e.g., confirmation emails):

```php
public function logoUrl(): string
{
    return $this->logo_path
        ? Storage::disk('public')->url($this->logo_path)
        : asset('images/hopo-logo.png');
}
```

---

## File Storage

- Disk: `public` (`storage/app/public/`)
- Path: `companies/{company_id}/logo.{ext}` — use `storeAs()` with a fixed filename (`logo.{ext}`) to avoid orphaned files when the format changes between uploads
- Accessible via: `/storage/companies/{id}/logo.{ext}` (existing symlink)
- On re-upload: `Storage::disk('public')->delete($company->logo_path)` before saving new file
- On delete: same deletion + set `logo_path = null`
- On company deletion: **out of scope** for this feature — `CompanyController::destroy()` currently prevents deleting companies that have locations, so this is low risk. A future cleanup task should address it.

---

## Admin UI — Company Edit Page

On the existing company edit form:

1. **Add `enctype` to form tag** — the edit form must include `enctype="multipart/form-data"` or the file binary will not be transmitted:
   ```blade
   <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
   ```

2. **Logo preview** — if `logo_path` is set, show a small preview of the current logo above the file input

3. **File input** — `<input type="file" name="logo" accept="image/png,image/jpeg,image/webp">`

4. **Delete logo button** — visible only when a logo exists; sends `DELETE /companies/{company}/logo`; reverts to Hopo fallback

---

## Controller & Routes

### `CompanyController::update()`

Add to existing validation rules:
```php
'logo' => 'nullable|file|image|mimes:png,jpg,jpeg,webp|max:2048',
```

> The `image` rule uses GD/Exif to verify the binary is actually an image, which defends against disguised payloads (e.g., a PHP file renamed to `.jpg`).

Add to existing update logic (run **after** `$company->update($validated)`):

```php
if ($request->hasFile('logo')) {
    if ($company->logo_path) {
        Storage::disk('public')->delete($company->logo_path);
    }
    // Derive extension from server-detected MIME type, not the client-supplied filename,
    // to prevent storing a file named logo.php on disk.
    $ext = match($request->file('logo')->getMimeType()) {
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',  // covers image/jpeg
    };
    $path = $request->file('logo')->storeAs("companies/{$company->id}", "logo.{$ext}", 'public');
    $company->logo_path = $path;
    $company->save();
}
```

### New: `CompanyController::deleteLogo()`

```php
public function deleteLogo(Company $company): RedirectResponse
{
    $this->authorize('update', $company);

    if ($company->logo_path) {
        Storage::disk('public')->delete($company->logo_path);
        $company->update(['logo_path' => null]);
    }

    return redirect()->back()->with('success', 'Logo sters.');
}
```

### New Route

Place **inside** the existing `auth` middleware group (same group as the companies resource):

```php
Route::delete('/companies/{company}/logo', [CompanyController::class, 'deleteLogo'])
    ->name('companies.logo.delete');
```

---

## Booking Layout

**File:** `resources/views/layouts/booking.blade.php`

Replace the hardcoded Hopo logo with a call to `$location->company->logoUrl()`. The existing `<a href="/">` wrapper around the logo (which linked to the Hopo homepage) is intentionally removed — on a company-branded booking page, linking back to the Hopo homepage is misleading.

```blade
<img src="{{ $location->company->logoUrl() }}" alt="{{ $location->company->name }}" class="h-12 w-auto">
```

---

## Eager Loading

To avoid N+1, add `$location->loadMissing('company')` in **both** methods of `PublicBookingController` that render the booking layout:

- `showForm()` — booking form view
- `confirmation()` — confirmation view

---

## Out of Scope

- Per-location logo override
- Automatic storage cleanup on company deletion
- Image resizing or optimization
- CDN / S3 integration (uses local public disk; S3 already configured in `filesystems.php` for future use)
