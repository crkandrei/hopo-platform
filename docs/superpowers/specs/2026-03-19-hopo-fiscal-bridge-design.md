# HopoFiscalBridge — Phone-Home Integration Design

**Date:** 2026-03-19
**Status:** Approved

## Overview

Extend the hopo-platform web application to support phone-home communication from `HopoFiscalBridge`, a Windows Service installed on each location's workstation. The bridge sends heartbeats, logs, and receives commands from the server. One location = one bridge.

The existing `bridge_config` JSON column on `locations` (unused in practice — bridge URL is hardcoded as `localhost:9000` in views) will be removed. The hardcoded `localhost:9000` references in views (`fiscal-receipts/index.blade.php`, `end-of-day/index.blade.php`, `layouts/app.blade.php`, `standalone-receipts/pay.blade.php`, `partials/payment-wizard-script.blade.php`) are **out of scope** for this work — they represent a separate, browser-to-local-bridge flow that coexists independently with the phone-home architecture defined here.

---

## 1. Database Schema

### New table: `location_bridges` (1:1 with locations)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| location_id | bigint FK → locations | unique, cascade delete |
| api_key | string(64) | unique, nullable — null = not configured |
| client_id | uuid string | nullable — set by bridge on first heartbeat |
| status | enum(online, offline, never_connected) | default never_connected — kept in sync by heartbeat and `bridges:mark-offline` command; UI badge reads this column |
| version | string | nullable |
| mode | enum(live, test) | nullable |
| last_seen_at | timestamp | nullable — always set by heartbeat alongside status |
| last_print_at | timestamp | nullable |
| print_count | integer | default 0 |
| z_report_count | integer | default 0 |
| error_count | integer | default 0 |
| uptime | integer (seconds) | nullable |
| created_at / updated_at | timestamps | |

### New table: `bridge_logs`

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| location_id | bigint FK → locations | cascade delete |
| level | enum(info, warn, error) | |
| message | text | |
| timestamp | timestamp | sent by bridge |
| created_at | timestamp | |

**Index:** composite index on `(location_id, created_at)` for efficient recent-logs queries.

### New table: `bridge_commands`

| Column | Type | Notes |
|---|---|---|
| id | uuid PK | serves as commandId — migration must use `$table->uuid('id')->primary()` (not `$table->id()`) |
| location_id | bigint FK → locations | cascade delete |
| command | enum(restart, set_config) | |
| payload | json | nullable |
| status | enum(pending, sent, completed, failed) | default pending |
| ack_message | string | nullable |
| created_at / updated_at | timestamps | |

### Changes to `locations`

- Drop column `bridge_config` (unused in practice, no data to migrate)
- Remove `bridge_config` from `$fillable`, `$casts`, and validation rules in `LocationController` (`store` and `update` methods)
- Add `hasOne(LocationBridge::class)` relation

Note: `bridge_logs` and `bridge_commands` use `location_id` directly (not via `location_bridges`) to avoid double joins.

---

## 2. API Endpoints (Bridge → Server)

All routes under `/api/bridges`, no `auth` middleware. Authentication via `BridgeApiAuth` middleware (applied as FQCN in route group — no alias registration needed in `bootstrap/app.php` in Laravel 11).

### Authentication Middleware: `BridgeApiAuth`

- Reads `Authorization: Bearer <key>` from request header
- Looks up `LocationBridge` where `api_key = key`
- Returns `401 Unauthorized` if not found
- Injects `$request->bridge` (LocationBridge) and `$request->bridgeLocation` (Location) for controllers

### `POST /api/bridges/heartbeat`

**Request body:**
```json
{
  "clientId": "uuid",
  "status": "online",
  "version": "1.2.0",
  "uptime": 3600,
  "bridgeMode": "live",
  "lastPrintAt": "2026-03-19T10:00:00Z",
  "printCount": 42,
  "zReportCount": 1,
  "errorCount": 3
}
```

**Actions:**
- Identify location via API key from header
- If `client_id` is null on bridge → set from `clientId` in body (first connection)
- Update all `bridge_*` fields on `location_bridges`; always set `last_seen_at = now()`
- **Response:** `200 OK`

### `POST /api/bridges/logs`

**Request body:**
```json
{
  "clientId": "uuid",
  "logs": [
    { "level": "info", "message": "Print success", "timestamp": "..." },
    { "level": "error", "message": "ECR timeout", "timestamp": "..." }
  ]
}
```

**Actions:**
- Identify location via API key from header
- Validate request: `logs` must be a non-empty array; each entry must have a valid `level` (info/warn/error), non-empty `message`, and valid `timestamp`
- If validation fails → `422 Unprocessable Entity`
- Bulk insert logs into `bridge_logs` with `location_id`
- **Response:** `200 OK`

### `GET /api/bridges/commands/{clientId}`

The `{clientId}` URL parameter is a convention from the Windows Service that also serves as an anti-replay check — it ensures the bridge cannot accidentally poll commands for another location even if the API key were shared.

**Actions:**
- Identify location via API key from header
- Verify `client_id` on bridge matches `{clientId}` in URL — return `403` if mismatch
- Find first `pending` command for `location_id`
- If none: **`204 No Content`**
- If found: mark status → `sent`, respond `200`:
  ```json
  { "commandId": "...", "command": "restart", "payload": null }
  ```

### `POST /api/bridges/commands/{clientId}/ack`

**Request body:**
```json
{ "commandId": "abc123", "success": true, "message": "Restarting..." }
```

**Actions:**
- Identify location via API key from header
- Find command by `commandId`; verify command's `location_id` matches the authenticated bridge's `location_id` — return `404` if not found or not owned
- Update status → `completed` or `failed`, save `ack_message`
- **Response:** `200 OK`

### File structure
```
app/Http/Controllers/Api/BridgeController.php
app/Http/Middleware/BridgeApiAuth.php
```

Routes added to `routes/api.php` in a dedicated group with `BridgeApiAuth` middleware applied as FQCN.

---

## 3. Admin UI — "Configurare Bridge" section on location edit page

Added to the bottom of `resources/views/locations/edit.blade.php`. Visible to Super Admin and Company Admin. Rendered as a distinct card below the main form.

**Note on route model binding:** The web routes use `{location}` as the route parameter. Because `Location::getRouteKeyName()` returns `'slug'`, Laravel resolves `{location}` via the slug. `LocationBridgeController` must type-hint `Location $location` to benefit from automatic slug binding.

### 3a. API Key

- Readonly text input displaying `api_key`, masked as `••••••••` by default
- "Arată" toggle button (Alpine.js `x-show` / `x-data`)
- **"Generează API Key"** button → `POST /locations/{location}/bridge/generate-key`
  - If key already exists: confirmation modal before regenerating (existing bridge will lose access)
  - If no key: generate directly (32 random bytes → 64-char hex string via `bin2hex(random_bytes(32))`)
  - On success: update input value without page reload (fetch + Alpine reactive data)

### 3b. Bridge Status

Displayed only when `location_bridges` record exists. The UI badge reads the persisted `status` column, which is maintained by:
- Heartbeat endpoint (sets `online`)
- `bridges:mark-offline` command (sets `offline`)

Display:
- Colored badge: green (online) / red (offline) / gray (never_connected)
- Last seen timestamp (formatted), version, mode (live/test)
- Counters: print_count, z_report_count, error_count

### 3c. Quick Commands

Buttons that POST to dedicated web routes:
- **"Restart bridge"** → creates `restart` command with status `pending`
- **"Mod test"** → creates `set_config` command with payload `{"BRIDGE_MODE": "test"}`
- **"Mod live"** → creates `set_config` command with payload `{"BRIDGE_MODE": "live"}`
- Flash feedback after each action

### 3d. Recent Logs

Table showing last 50 `bridge_logs` for the location, ordered by `created_at DESC`:
- Columns: timestamp, level (colored badge), message
- Hidden if no logs exist

### Web routes (with `auth` middleware)
```
POST /locations/{location}/bridge/generate-key  → LocationBridgeController@generateKey
POST /locations/{location}/bridge/commands      → LocationBridgeController@createCommand
```

**Authorization:** `LocationBridgeController` calls `$this->authorize('update', $location)` at the start of each action — consistent with the existing pattern in `LocationController`. This ensures a Company Admin can only manage bridges for locations within their own company.

```
app/Http/Controllers/LocationBridgeController.php
```

---

## 4. Periodic Job — Mark Bridges Offline

**Artisan command:** `bridges:mark-offline`
**File:** `app/Console/Commands/MarkBridgesOffline.php`
**Schedule:** every minute in `routes/console.php`

Logic:
- Select all `location_bridges` where `status != 'offline'` AND `last_seen_at IS NOT NULL` AND `last_seen_at < now() - 90 seconds`
- Bulk update → `status = 'offline'`
- Records with `status = 'never_connected'` have `last_seen_at = NULL` and are naturally excluded by the `IS NOT NULL` condition

Note: heartbeat always sets `last_seen_at = now()`, so `status = 'online'` records will never have a null `last_seen_at`.

---

## 5. Migration Plan

1. Create migration: `create_location_bridges_table`
2. Create migration: `create_bridge_logs_table` (with composite index on `location_id, created_at`)
3. Create migration: `create_bridge_commands_table` (UUID primary key via `$table->uuid('id')->primary()`)
4. Create migration: `drop_bridge_config_from_locations_table` (no data migration needed — field unused)
5. Update `Location` model: remove `bridge_config` from `$fillable`, `$casts`; add `hasOne(LocationBridge::class)`
6. Update `LocationController`: remove `bridge_config` validation rule from `store` and `update`
7. Create `LocationBridge` model with relationships
8. Create `BridgeLog` model
9. Create `BridgeCommand` model

---

## 6. File Inventory

### New files
```
database/migrations/YYYY_MM_DD_create_location_bridges_table.php
database/migrations/YYYY_MM_DD_create_bridge_logs_table.php
database/migrations/YYYY_MM_DD_create_bridge_commands_table.php
database/migrations/YYYY_MM_DD_drop_bridge_config_from_locations_table.php
app/Models/LocationBridge.php
app/Models/BridgeLog.php
app/Models/BridgeCommand.php
app/Http/Controllers/Api/BridgeController.php
app/Http/Controllers/LocationBridgeController.php
app/Http/Middleware/BridgeApiAuth.php
app/Console/Commands/MarkBridgesOffline.php
```

### Modified files
```
app/Models/Location.php                    — remove bridge_config, add hasOne bridge
app/Http/Controllers/LocationController.php — remove bridge_config validation rules
routes/api.php                             — add /bridges/* routes
routes/web.php                             — add bridge admin routes
routes/console.php                         — add schedule for bridges:mark-offline
resources/views/locations/edit.blade.php   — add "Configurare Bridge" section
```
