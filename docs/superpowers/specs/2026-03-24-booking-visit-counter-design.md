# Booking Visit Counter — Design Spec

## Goal

Track how many unique sessions have visited a location's public booking page. Display the counter to super admins only in the location detail view.

## Architecture

Single integer counter on the `locations` table, incremented once per session per location. No separate table, no cache dependency.

## Components

### 1. Migration
- Class name: `AddBookingVisitCountToLocationsTable`
- Add `booking_visit_count` as `unsignedInteger`, default 0, not nullable, to `locations` table.
- `unsignedInteger` (4 bytes, max ~4.2 billion) is appropriate for a per-location visit counter.

### 2. PublicBookingController — showForm()
- The increment happens **after** the existing 404 guard (after the halls/packages check), immediately before the `return view(...)` call:
  - Check `session()->has("visited_booking_{$location->id}")`
  - If false: call `$location->increment('booking_visit_count')` and `session()->put("visited_booking_{$location->id}", true)`
  - If true: do nothing
- Session key uses `$location->id` (numeric primary key) because it is immutable and collision-free regardless of slug changes.
- `increment()` issues an atomic SQL `UPDATE locations SET booking_visit_count = booking_visit_count + 1`, avoiding a read-modify-write race at the DB level.
- Known limitation: if two parallel requests arrive before either session write completes (e.g., same user opening two tabs simultaneously), both could call `increment()`. This is acceptable for a best-effort counter.

### 3. Location model
- No `$fillable` change needed — `increment()` bypasses mass assignment entirely.
- Add `'booking_visit_count' => 'integer'` to the `$casts` array for type consistency.

### 4. Location show view — `resources/views/locations/show.blade.php`
- This view is behind auth middleware — unauthenticated users cannot reach it.
- Add after the booking URL display block inside the "Aniversări și Booking" section, as a new sibling `<div class="pt-4 border-t border-gray-200">`:
  ```blade
  @if(Auth::user() && Auth::user()->isSuperAdmin())
  <div class="pt-4 border-t border-gray-200">
      <p class="text-sm text-gray-600">Vizite pagină booking: <span class="font-semibold text-gray-900">{{ number_format($location->booking_visit_count) }}</span></p>
  </div>
  @endif
  ```
- Guard: super admin only — must **not** include `isCompanyAdmin()`.
- `$location` is already passed to this view by `LocationController::show()`.

## Constraints

- Counter is **not** decremented on any action.
- Bots and crawlers are not filtered — best-effort counter only.
- Counter resets only via direct DB intervention (no UI reset button).
- Only `showForm()` is counted — only after the 404 guard passes. Confirmation page and API endpoints are not counted.

## Testing

- Feature: first visit increments counter by 1.
- Feature: second visit in same session does not increment again.
- Feature: two different sessions each increment the counter once.
- Feature: visiting a location with no halls/packages configured (404 path) does not increment the counter.
- Feature: counter is visible in location show for super admin.
- Feature: counter is not visible for company admin.
- Feature: counter is not visible for location staff (or any non-super-admin role).
- Unit: `booking_visit_count` is cast to integer on the Location model.
- Migration: column defaults to 0.
