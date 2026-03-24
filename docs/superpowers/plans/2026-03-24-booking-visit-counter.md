# Booking Visit Counter Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Track unique session visits to each location's public booking page and display the count to super admins in the location detail view.

**Architecture:** Add a `booking_visit_count` integer column to `locations`, increment it once per Laravel session per location in `PublicBookingController::showForm()` (after the 404 guard), and display the counter in `locations/show.blade.php` behind a super-admin guard.

**Tech Stack:** Laravel 12, PHP 8.x, MySQL, Blade, Tailwind CSS.

---

## Files

| Action | Path |
|--------|------|
| Create | `database/migrations/2026_03_24_100000_add_booking_visit_count_to_locations_table.php` |
| Modify | `app/Models/Location.php` — add `'booking_visit_count' => 'integer'` to `$casts` |
| Modify | `app/Http/Controllers/PublicBookingController.php` — increment counter in `showForm()` |
| Modify | `resources/views/locations/show.blade.php` — display counter for super admin |
| Create | `tests/Feature/BookingVisitCounterTest.php` |

---

## Task 1: Migration and model cast

**Files:**
- Create: `database/migrations/2026_03_24_100000_add_booking_visit_count_to_locations_table.php`
- Modify: `app/Models/Location.php:30-37`
- Create: `tests/Feature/BookingVisitCounterTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/BookingVisitCounterTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingVisitCounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_has_booking_visit_count_defaulting_to_zero(): void
    {
        $location = Location::factory()->create();

        $this->assertSame(0, $location->fresh()->booking_visit_count);
    }

    public function test_booking_visit_count_is_cast_to_integer(): void
    {
        $location = Location::factory()->create();

        $this->assertIsInt($location->fresh()->booking_visit_count);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/BookingVisitCounterTest.php
```

Expected: FAIL — column does not exist yet.

- [ ] **Step 3: Create the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedInteger('booking_visit_count')->default(0)->after('birthday_concurrent_reservations');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('booking_visit_count');
        });
    }
};
```

- [ ] **Step 4: Add cast to Location model**

In `app/Models/Location.php`, add to `$casts` array:

```php
protected $casts = [
    'is_active' => 'boolean',
    'bracelet_required' => 'boolean',
    'fiscal_enabled' => 'boolean',
    'birthday_concurrent_reservations' => 'boolean',
    'price_per_hour' => 'decimal:2',
    'overflow_price_per_hour' => 'decimal:2',
    'booking_visit_count' => 'integer',
];
```

- [ ] **Step 5: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 6: Run tests to verify they pass**

```bash
php artisan test tests/Feature/BookingVisitCounterTest.php
```

Expected: 2 tests passing.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_03_24_100000_add_booking_visit_count_to_locations_table.php app/Models/Location.php tests/Feature/BookingVisitCounterTest.php
git commit -m "feat: add booking_visit_count column to locations and cast"
```

---

## Task 2: Visit tracking in PublicBookingController

**Files:**
- Modify: `app/Http/Controllers/PublicBookingController.php:19-57`
- Modify: `tests/Feature/BookingVisitCounterTest.php`

This task adds the session-based increment logic. The increment must happen **after** the `abort(404)` guard on lines 25-27, immediately before the `return view(...)`.

- [ ] **Step 1: Write the failing tests**

Add these test methods to `tests/Feature/BookingVisitCounterTest.php`. Note: the booking page returns 404 if no halls/packages are configured, so we need a helper to set up the minimum required data (see pattern from `CompanyLogoTest::setUpBookingLocation()`).

```php
    // --- visit tracking tests ---

    private function makeBookingLocation(): Location
    {
        $company  = \App\Models\Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
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
        return $location;
    }

    public function test_first_visit_increments_booking_visit_count(): void
    {
        $this->withoutVite();
        $location = $this->makeBookingLocation();

        $this->get(route('booking.show', $location));

        $this->assertSame(1, $location->fresh()->booking_visit_count);
    }

    public function test_same_session_does_not_increment_twice(): void
    {
        $this->withoutVite();
        $location = $this->makeBookingLocation();

        $this->get(route('booking.show', $location));
        $this->get(route('booking.show', $location));

        $this->assertSame(1, $location->fresh()->booking_visit_count);
    }

    public function test_two_different_sessions_each_increment_once(): void
    {
        $this->withoutVite();
        $location = $this->makeBookingLocation();

        // First session
        $this->get(route('booking.show', $location));
        // Second session (new test client = new session)
        $this->refreshApplication();
        $this->get(route('booking.show', $location));

        $this->assertSame(2, $location->fresh()->booking_visit_count);
    }

    public function test_visiting_unconfigured_location_does_not_increment(): void
    {
        // Location with no halls/packages → abort(404) → counter must not increment
        $location = Location::factory()->create();

        $this->get(route('booking.show', $location))->assertNotFound();

        $this->assertSame(0, $location->fresh()->booking_visit_count);
    }
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/BookingVisitCounterTest.php --filter="increment\|session\|unconfigured"
```

Expected: FAIL — counter stays at 0.

- [ ] **Step 3: Add increment logic to PublicBookingController**

In `app/Http/Controllers/PublicBookingController.php`, add the increment block between `$singleHall = $halls->count() === 1;` and `return view(...)`:

```php
        $singleHall = $halls->count() === 1;

        // Track unique session visits
        $sessionKey = "visited_booking_{$location->id}";
        if (! session()->has($sessionKey)) {
            $location->increment('booking_visit_count');
            session()->put($sessionKey, true);
        }

        return view('booking.show', [
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test tests/Feature/BookingVisitCounterTest.php
```

Expected: all tests passing.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PublicBookingController.php tests/Feature/BookingVisitCounterTest.php
git commit -m "feat: track unique session visits on booking page"
```

---

## Task 3: Display counter in location show view

**Files:**
- Modify: `resources/views/locations/show.blade.php:197-198`
- Modify: `tests/Feature/BookingVisitCounterTest.php`

The counter is added as a new `<div>` sibling after the booking URL block (which closes at line 197), still inside the "Aniversări și Booking" card that closes at line 198.

- [ ] **Step 1: Write the failing tests**

Add these test methods to `tests/Feature/BookingVisitCounterTest.php`:

```php
    // --- view visibility tests ---

    private function makeSuperAdmin(): \App\Models\User
    {
        $role = \App\Models\Role::where('name', 'SUPER_ADMIN')->first();
        return \App\Models\User::factory()->create(['role_id' => $role->id, 'status' => 'active']);
    }

    private function makeCompanyAdmin(\App\Models\Company $company): \App\Models\User
    {
        $role = \App\Models\Role::where('name', 'COMPANY_ADMIN')->first();
        return \App\Models\User::factory()->create(['role_id' => $role->id, 'company_id' => $company->id, 'status' => 'active']);
    }

    private function makeStaff(\App\Models\Location $location): \App\Models\User
    {
        $role = \App\Models\Role::where('name', 'STAFF')->first();
        return \App\Models\User::factory()->create(['role_id' => $role->id, 'location_id' => $location->id, 'status' => 'active']);
    }

    public function test_super_admin_sees_booking_visit_count_in_location_show(): void
    {
        $this->withoutVite();
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create(['booking_visit_count' => 42]);

        $this->actingAs($admin)
            ->get(route('locations.show', $location))
            ->assertSee('Vizite pagină booking')
            ->assertSee('42');
    }

    public function test_company_admin_does_not_see_booking_visit_count(): void
    {
        $this->withoutVite();
        $company  = \App\Models\Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id, 'booking_visit_count' => 42]);
        $admin    = $this->makeCompanyAdmin($company);

        $this->actingAs($admin)
            ->get(route('locations.show', $location))
            ->assertDontSee('Vizite pagină booking');
    }

    public function test_staff_does_not_see_booking_visit_count(): void
    {
        $this->withoutVite();
        $company  = \App\Models\Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id, 'booking_visit_count' => 42]);
        $staff    = $this->makeStaff($location);

        $this->actingAs($staff)
            ->get(route('locations.show', $location))
            ->assertDontSee('Vizite pagină booking');
    }
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/BookingVisitCounterTest.php --filter="sees\|does_not_see"
```

Expected: FAIL — text not present in view yet.

- [ ] **Step 3: Add counter to locations/show.blade.php**

In `resources/views/locations/show.blade.php`, add after the closing `</div>` of the booking URL block (after line 196's `</div>`) and before the section's closing `</div>` (line 198):

```blade
        @if(Auth::user() && Auth::user()->isSuperAdmin())
        <div class="pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-600">Vizite pagină booking: <span class="font-semibold text-gray-900">{{ number_format($location->booking_visit_count) }}</span></p>
        </div>
        @endif
```

- [ ] **Step 4: Run all tests to verify they pass**

```bash
php artisan test tests/Feature/BookingVisitCounterTest.php
```

Expected: all tests passing.

- [ ] **Step 5: Commit**

```bash
git add resources/views/locations/show.blade.php tests/Feature/BookingVisitCounterTest.php
git commit -m "feat: display booking visit counter for super admin in location show"
```
