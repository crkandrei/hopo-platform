# Booking Visit Counter — Design Spec

## Goal

Track how many unique sessions have visited a location's public booking page. Display the counter to super admins in the location detail view.

## Architecture

Single integer counter on the `locations` table, incremented once per session per location. No separate table, no cache dependency.

## Components

### 1. Migration
- Add `booking_visit_count` unsigned integer, default 0, nullable false, to `locations` table.

### 2. PublicBookingController — showForm()
- At the start of `showForm(Location $location)`, before any other logic:
  - Check `session()->has("visited_booking_{$location->id}")`
  - If false: call `$location->increment('booking_visit_count')` and `session()->put("visited_booking_{$location->id}", true)`
  - If true: do nothing
- Session key is per-location so visiting multiple locations' booking pages each count once.

### 3. Location model
- Add `booking_visit_count` to `$fillable` (or keep default 0 via migration — no fillable change needed since we use `increment()`).

### 4. Location show view — `resources/views/locations/show.blade.php`
- Add inside an `@if(Auth::user()->isSuperAdmin())` block:
  ```
  Vizite pagină booking: {{ number_format($location->booking_visit_count) }}
  ```
- Styled as a simple stat badge consistent with existing UI patterns in that view.

## Constraints

- Counter is **not** decremented on any action.
- Bots and crawlers are not filtered — this is a simple best-effort counter.
- Counter resets only via direct DB intervention (no UI reset button).
- Only `showForm()` is counted — not the confirmation page or API endpoints.

## Testing

- Unit: `Location` factory creates with `booking_visit_count = 0` by default.
- Feature: first visit increments counter; second visit in same session does not; two different sessions each increment once.
- Feature: counter is visible in location show for super admin; not visible for other roles.
