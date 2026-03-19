# HopoFiscalBridge Phone-Home Integration — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add phone-home support for the HopoFiscalBridge Windows Service — including heartbeat, logs, and commands API endpoints, plus an admin UI section on the location edit page.

**Architecture:** A new `location_bridges` table (1:1 with `locations`) holds all bridge state. A `BridgeApiAuth` middleware authenticates microservice requests via Bearer API key. The admin UI lives in `locations/edit.blade.php` and is managed by a dedicated `LocationBridgeController`.

**Tech Stack:** Laravel 11, PHP, MySQL, PHPUnit (Feature tests with RefreshDatabase), Blade + Alpine.js

---

## File Map

### New files
```
database/migrations/*_create_location_bridges_table.php
database/migrations/*_create_bridge_logs_table.php
database/migrations/*_create_bridge_commands_table.php
database/migrations/*_drop_bridge_config_from_locations_table.php
app/Models/LocationBridge.php
app/Models/BridgeLog.php
app/Models/BridgeCommand.php
database/factories/LocationBridgeFactory.php
app/Http/Middleware/BridgeApiAuth.php
app/Http/Controllers/Api/BridgeController.php
app/Http/Controllers/LocationBridgeController.php
app/Console/Commands/MarkBridgesOffline.php
tests/Feature/BridgeApiTest.php
tests/Feature/LocationBridgeAdminTest.php
tests/Unit/MarkBridgesOfflineTest.php
```

### Modified files
```
app/Models/Location.php                      — remove bridge_config, add hasOne(LocationBridge)
app/Http/Controllers/LocationController.php  — remove bridge_config validation rules
routes/api.php                               — add /bridges/* route group
routes/web.php                               — add bridge admin web routes
routes/console.php                           — add bridges:mark-offline schedule
resources/views/locations/edit.blade.php     — add "Configurare Bridge" section
```

---

## Task 1: Database Migrations

**Files:**
- Create: `database/migrations/2026_03_19_100000_create_location_bridges_table.php`
- Create: `database/migrations/2026_03_19_100001_create_bridge_logs_table.php`
- Create: `database/migrations/2026_03_19_100002_create_bridge_commands_table.php`
- Create: `database/migrations/2026_03_19_100003_drop_bridge_config_from_locations_table.php`

- [ ] **Step 1: Create location_bridges migration**

```php
<?php
// database/migrations/2026_03_19_100000_create_location_bridges_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_bridges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('api_key', 64)->unique()->nullable();
            $table->string('client_id')->nullable();
            $table->enum('status', ['online', 'offline', 'never_connected'])->default('never_connected');
            $table->string('version')->nullable();
            $table->enum('mode', ['live', 'test'])->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_print_at')->nullable();
            $table->integer('print_count')->default(0);
            $table->integer('z_report_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->integer('uptime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_bridges');
    }
};
```

- [ ] **Step 2: Create bridge_logs migration**

```php
<?php
// database/migrations/2026_03_19_100001_create_bridge_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bridge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['info', 'warn', 'error']);
            $table->text('message');
            $table->timestamp('timestamp');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['location_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_logs');
    }
};
```

- [ ] **Step 3: Create bridge_commands migration**

```php
<?php
// database/migrations/2026_03_19_100002_create_bridge_commands_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bridge_commands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->enum('command', ['restart', 'set_config']);
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'sent', 'completed', 'failed'])->default('pending');
            $table->string('ack_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_commands');
    }
};
```

- [ ] **Step 4: Create drop bridge_config migration**

```php
<?php
// database/migrations/2026_03_19_100003_drop_bridge_config_from_locations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('bridge_config');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->json('bridge_config')->nullable();
        });
    }
};
```

- [ ] **Step 5: Run migrations and verify they pass**

```bash
php artisan migrate
```

Expected: all 4 migrations run without errors.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_03_19_100000_create_location_bridges_table.php \
        database/migrations/2026_03_19_100001_create_bridge_logs_table.php \
        database/migrations/2026_03_19_100002_create_bridge_commands_table.php \
        database/migrations/2026_03_19_100003_drop_bridge_config_from_locations_table.php
git commit -m "feat: add location_bridges, bridge_logs, bridge_commands migrations; drop bridge_config"
```

---

## Task 2: Models

**Files:**
- Create: `app/Models/LocationBridge.php`
- Create: `app/Models/BridgeLog.php`
- Create: `app/Models/BridgeCommand.php`
- Create: `database/factories/LocationBridgeFactory.php`
- Modify: `app/Models/Location.php`

- [ ] **Step 1: Create LocationBridge model**

```php
<?php
// app/Models/LocationBridge.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationBridge extends Model
{
    protected $fillable = [
        'location_id',
        'api_key',
        'client_id',
        'status',
        'version',
        'mode',
        'last_seen_at',
        'last_print_at',
        'print_count',
        'z_report_count',
        'error_count',
        'uptime',
    ];

    protected $casts = [
        'last_seen_at'   => 'datetime',
        'last_print_at'  => 'datetime',
        'print_count'    => 'integer',
        'z_report_count' => 'integer',
        'error_count'    => 'integer',
        'uptime'         => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(BridgeCommand::class, 'location_id', 'location_id');
    }
}
```

- [ ] **Step 2: Create BridgeLog model**

```php
<?php
// app/Models/BridgeLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BridgeLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'location_id',
        'level',
        'message',
        'timestamp',
        'created_at',
    ];

    protected $casts = [
        'timestamp'  => 'datetime',
        'created_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
```

- [ ] **Step 3: Create BridgeCommand model**

```php
<?php
// app/Models/BridgeCommand.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BridgeCommand extends Model
{
    use HasUuids;

    protected $fillable = [
        'location_id',
        'command',
        'payload',
        'status',
        'ack_message',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
```

- [ ] **Step 4: Create LocationBridgeFactory**

```php
<?php
// database/factories/LocationBridgeFactory.php

namespace Database\Factories;

use App\Models\Location;
use App\Models\LocationBridge;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationBridgeFactory extends Factory
{
    protected $model = LocationBridge::class;

    public function definition(): array
    {
        return [
            'location_id'    => Location::factory(),
            'api_key'        => bin2hex(random_bytes(32)),
            'client_id'      => $this->faker->uuid(),
            'status'         => 'never_connected',
            'version'        => null,
            'mode'           => null,
            'last_seen_at'   => null,
            'last_print_at'  => null,
            'print_count'    => 0,
            'z_report_count' => 0,
            'error_count'    => 0,
            'uptime'         => null,
        ];
    }

    public function online(): static
    {
        return $this->state([
            'status'       => 'online',
            'last_seen_at' => now(),
        ]);
    }

    public function offline(): static
    {
        return $this->state([
            'status'       => 'offline',
            'last_seen_at' => now()->subMinutes(5),
        ]);
    }
}
```

- [ ] **Step 5: Update Location model — remove bridge_config, add hasOne**

In `app/Models/Location.php`:

Remove `'bridge_config'` from `$fillable` and from `$casts`. Add the `bridge()` relationship method:

```php
// Add this import at the top:
use Illuminate\Database\Eloquent\Relations\HasOne;

// Add this method inside the class (after standaloneReceipts()):
public function bridge(): HasOne
{
    return $this->hasOne(LocationBridge::class);
}
```

- [ ] **Step 6: Commit**

```bash
git add app/Models/LocationBridge.php app/Models/BridgeLog.php app/Models/BridgeCommand.php \
        database/factories/LocationBridgeFactory.php app/Models/Location.php
git commit -m "feat: add LocationBridge, BridgeLog, BridgeCommand models and factory"
```

---

## Task 3: Clean Up LocationController

**Files:**
- Modify: `app/Http/Controllers/LocationController.php`

- [ ] **Step 1: Remove bridge_config from validation in store() and update()**

In `app/Http/Controllers/LocationController.php`, remove the line:
```php
'bridge_config' => 'nullable|array',
```
from both the `store()` method (around line 70) and the `update()` method (around line 153).

- [ ] **Step 2: Verify no other references to bridge_config remain in the controller**

```bash
grep -n "bridge_config" app/Http/Controllers/LocationController.php
```

Expected: no output.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/LocationController.php
git commit -m "chore: remove bridge_config validation from LocationController"
```

---

## Task 4: BridgeApiAuth Middleware

**Files:**
- Create: `app/Http/Middleware/BridgeApiAuth.php`
- Test: `tests/Feature/BridgeApiTest.php` (first tests only)

- [ ] **Step 1: Write failing tests for middleware**

Create `tests/Feature/BridgeApiTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Location;
use App\Models\LocationBridge;
use Tests\TestCase;

class BridgeApiTest extends TestCase
{
    // ── Middleware ────────────────────────────────────────────────────────────

    public function test_heartbeat_returns_401_when_no_authorization_header(): void
    {
        $response = $this->postJson('/api/bridges/heartbeat', []);

        $response->assertStatus(401);
    }

    public function test_heartbeat_returns_401_when_api_key_is_invalid(): void
    {
        $response = $this->postJson('/api/bridges/heartbeat', [], [
            'Authorization' => 'Bearer invalid-key',
        ]);

        $response->assertStatus(401);
    }

    public function test_heartbeat_returns_401_when_api_key_is_null_on_bridge(): void
    {
        $bridge = LocationBridge::factory()->create(['api_key' => null]);

        $response = $this->postJson('/api/bridges/heartbeat', [], [
            'Authorization' => 'Bearer ',
        ]);

        $response->assertStatus(401);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_heartbeat_returns_401"
```

Expected: FAIL — routes do not exist yet (404 or route not found error).

- [ ] **Step 3: Create BridgeApiAuth middleware**

```php
<?php
// app/Http/Middleware/BridgeApiAuth.php

namespace App\Http\Middleware;

use App\Models\LocationBridge;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BridgeApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (!str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $key = substr($header, 7);

        if (empty($key)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $bridge = LocationBridge::where('api_key', $key)->with('location')->first();

        if (!$bridge) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->attributes->set('bridge', $bridge);
        $request->attributes->set('bridgeLocation', $bridge->location);

        return $next($request);
    }
}
```

- [ ] **Step 4: Register routes with middleware (stub controller)**

In `routes/api.php`, add the following group at the bottom:

```php
use App\Http\Controllers\Api\BridgeController;
use App\Http\Middleware\BridgeApiAuth;

Route::middleware(BridgeApiAuth::class)->prefix('bridges')->group(function () {
    Route::post('/heartbeat', [BridgeController::class, 'heartbeat']);
    Route::post('/logs', [BridgeController::class, 'logs']);
    Route::get('/commands/{clientId}', [BridgeController::class, 'pollCommands']);
    Route::post('/commands/{clientId}/ack', [BridgeController::class, 'ackCommand']);
});
```

Create a stub controller so routes resolve:

```php
<?php
// app/Http/Controllers/Api/BridgeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BridgeController extends Controller
{
    public function heartbeat(Request $request)
    {
        return response()->json(['ok' => true]);
    }

    public function logs(Request $request)
    {
        return response()->json(['ok' => true]);
    }

    public function pollCommands(Request $request, string $clientId)
    {
        return response()->noContent();
    }

    public function ackCommand(Request $request, string $clientId)
    {
        return response()->json(['ok' => true]);
    }
}
```

Also create the `Api` directory if it doesn't exist:

```bash
mkdir -p app/Http/Controllers/Api
```

- [ ] **Step 5: Run middleware tests**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_heartbeat_returns_401"
```

Expected: all 3 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/BridgeApiAuth.php \
        app/Http/Controllers/Api/BridgeController.php \
        routes/api.php \
        tests/Feature/BridgeApiTest.php
git commit -m "feat: add BridgeApiAuth middleware and stub BridgeController"
```

---

## Task 5: Heartbeat Endpoint

**Files:**
- Modify: `app/Http/Controllers/Api/BridgeController.php`
- Test: `tests/Feature/BridgeApiTest.php` (append)

- [ ] **Step 1: Write failing tests for heartbeat**

Append to `tests/Feature/BridgeApiTest.php`:

```php
    // ── Heartbeat ─────────────────────────────────────────────────────────────

    private function bridgeWithKey(): LocationBridge
    {
        return LocationBridge::factory()->create([
            'api_key'   => 'test-api-key-1234',
            'client_id' => null,
        ]);
    }

    public function test_heartbeat_updates_bridge_fields(): void
    {
        $bridge = $this->bridgeWithKey();

        $response = $this->postJson('/api/bridges/heartbeat', [
            'clientId'    => 'abc-uuid',
            'status'      => 'online',
            'version'     => '1.2.0',
            'uptime'      => 3600,
            'bridgeMode'  => 'live',
            'lastPrintAt' => '2026-03-19T10:00:00Z',
            'printCount'  => 42,
            'zReportCount' => 1,
            'errorCount'  => 3,
        ], ['Authorization' => 'Bearer test-api-key-1234']);

        $response->assertStatus(200);

        $bridge->refresh();
        $this->assertEquals('abc-uuid', $bridge->client_id);
        $this->assertEquals('online', $bridge->status);
        $this->assertEquals('1.2.0', $bridge->version);
        $this->assertEquals(3600, $bridge->uptime);
        $this->assertEquals('live', $bridge->mode);
        $this->assertEquals(42, $bridge->print_count);
        $this->assertEquals(1, $bridge->z_report_count);
        $this->assertEquals(3, $bridge->error_count);
        $this->assertNotNull($bridge->last_seen_at);
        $this->assertNotNull($bridge->last_print_at);
    }

    public function test_heartbeat_does_not_overwrite_existing_client_id(): void
    {
        $bridge = LocationBridge::factory()->create([
            'api_key'   => 'test-key-existing-client',
            'client_id' => 'original-uuid',
        ]);

        $this->postJson('/api/bridges/heartbeat', [
            'clientId' => 'different-uuid',
            'status'   => 'online',
        ], ['Authorization' => 'Bearer test-key-existing-client']);

        $bridge->refresh();
        $this->assertEquals('original-uuid', $bridge->client_id);
    }

    public function test_heartbeat_always_sets_last_seen_at(): void
    {
        $bridge = $this->bridgeWithKey();

        $this->postJson('/api/bridges/heartbeat', [
            'clientId' => 'some-uuid',
            'status'   => 'online',
        ], ['Authorization' => 'Bearer test-api-key-1234']);

        $bridge->refresh();
        $this->assertNotNull($bridge->last_seen_at);
        $this->assertTrue($bridge->last_seen_at->diffInSeconds(now()) < 5);
    }
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_heartbeat"
```

Expected: test_heartbeat_returns_401* PASS, others FAIL (stub returns 200 but does not update DB).

- [ ] **Step 3: Implement heartbeat in BridgeController**

Replace the `heartbeat()` stub method:

```php
public function heartbeat(Request $request): \Illuminate\Http\JsonResponse
{
    $bridge = $request->attributes->get('bridge');

    $data = $request->validate([
        'clientId'     => 'required|string',
        'status'       => 'sometimes|string',
        'version'      => 'sometimes|nullable|string',
        'uptime'       => 'sometimes|nullable|integer',
        'bridgeMode'   => 'sometimes|nullable|string|in:live,test',
        'lastPrintAt'  => 'sometimes|nullable|date',
        'printCount'   => 'sometimes|nullable|integer',
        'zReportCount' => 'sometimes|nullable|integer',
        'errorCount'   => 'sometimes|nullable|integer',
    ]);

    $updateData = [
        'status'       => 'online',
        'last_seen_at' => now(),
    ];

    if (isset($data['version']))      $updateData['version']        = $data['version'];
    if (isset($data['uptime']))       $updateData['uptime']         = $data['uptime'];
    if (isset($data['bridgeMode']))   $updateData['mode']           = $data['bridgeMode'];
    if (isset($data['lastPrintAt']))  $updateData['last_print_at']  = $data['lastPrintAt'];
    if (isset($data['printCount']))   $updateData['print_count']    = $data['printCount'];
    if (isset($data['zReportCount'])) $updateData['z_report_count'] = $data['zReportCount'];
    if (isset($data['errorCount']))   $updateData['error_count']    = $data['errorCount'];

    // First heartbeat: set client_id
    if (is_null($bridge->client_id)) {
        $updateData['client_id'] = $data['clientId'];
    }

    $bridge->update($updateData);

    return response()->json(['ok' => true]);
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_heartbeat"
```

Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/BridgeController.php tests/Feature/BridgeApiTest.php
git commit -m "feat: implement heartbeat endpoint"
```

---

## Task 6: Logs Endpoint

**Files:**
- Modify: `app/Http/Controllers/Api/BridgeController.php`
- Test: `tests/Feature/BridgeApiTest.php` (append)

- [ ] **Step 1: Write failing tests for logs**

Append to `tests/Feature/BridgeApiTest.php`:

```php
    // ── Logs ──────────────────────────────────────────────────────────────────

    public function test_logs_inserts_entries_into_bridge_logs(): void
    {
        $bridge = LocationBridge::factory()->create(['api_key' => 'logs-test-key']);

        $response = $this->postJson('/api/bridges/logs', [
            'clientId' => 'some-uuid',
            'logs'     => [
                ['level' => 'info',  'message' => 'Print ok',       'timestamp' => '2026-03-19T10:00:00Z'],
                ['level' => 'error', 'message' => 'ECR timeout',    'timestamp' => '2026-03-19T10:01:00Z'],
            ],
        ], ['Authorization' => 'Bearer logs-test-key']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('bridge_logs', [
            'location_id' => $bridge->location_id,
            'level'       => 'info',
            'message'     => 'Print ok',
        ]);
        $this->assertDatabaseHas('bridge_logs', [
            'location_id' => $bridge->location_id,
            'level'       => 'error',
            'message'     => 'ECR timeout',
        ]);
    }

    public function test_logs_returns_422_when_logs_array_is_missing(): void
    {
        $bridge = LocationBridge::factory()->create(['api_key' => 'logs-validation-key']);

        $response = $this->postJson('/api/bridges/logs', [
            'clientId' => 'uuid',
        ], ['Authorization' => 'Bearer logs-validation-key']);

        $response->assertStatus(422);
    }

    public function test_logs_returns_422_when_log_level_is_invalid(): void
    {
        $bridge = LocationBridge::factory()->create(['api_key' => 'logs-level-key']);

        $response = $this->postJson('/api/bridges/logs', [
            'clientId' => 'uuid',
            'logs'     => [
                ['level' => 'debug', 'message' => 'test', 'timestamp' => '2026-03-19T10:00:00Z'],
            ],
        ], ['Authorization' => 'Bearer logs-level-key']);

        $response->assertStatus(422);
    }
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_logs"
```

Expected: FAIL.

- [ ] **Step 3: Implement logs() in BridgeController**

Replace the `logs()` stub method:

```php
public function logs(Request $request): \Illuminate\Http\JsonResponse
{
    $bridge = $request->attributes->get('bridge');

    $data = $request->validate([
        'clientId'             => 'required|string',
        'logs'                 => 'required|array|min:1',
        'logs.*.level'         => 'required|in:info,warn,error',
        'logs.*.message'       => 'required|string',
        'logs.*.timestamp'     => 'required|date',
    ]);

    $now  = now();
    $rows = array_map(fn($log) => [
        'location_id' => $bridge->location_id,
        'level'       => $log['level'],
        'message'     => $log['message'],
        'timestamp'   => $log['timestamp'],
        'created_at'  => $now,
    ], $data['logs']);

    \App\Models\BridgeLog::insert($rows);

    return response()->json(['ok' => true]);
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_logs"
```

Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/BridgeController.php tests/Feature/BridgeApiTest.php
git commit -m "feat: implement bridge logs endpoint"
```

---

## Task 7: Poll Commands Endpoint

**Files:**
- Modify: `app/Http/Controllers/Api/BridgeController.php`
- Test: `tests/Feature/BridgeApiTest.php` (append)

- [ ] **Step 1: Write failing tests for pollCommands**

Append to `tests/Feature/BridgeApiTest.php`:

```php
    // ── Poll Commands ─────────────────────────────────────────────────────────

    public function test_poll_commands_returns_204_when_no_pending_commands(): void
    {
        $bridge = LocationBridge::factory()->create([
            'api_key'   => 'poll-no-cmd-key',
            'client_id' => 'client-uuid-1',
        ]);

        $response = $this->getJson(
            '/api/bridges/commands/client-uuid-1',
            ['Authorization' => 'Bearer poll-no-cmd-key']
        );

        $response->assertStatus(204);
    }

    public function test_poll_commands_returns_command_and_marks_it_sent(): void
    {
        $bridge = LocationBridge::factory()->create([
            'api_key'   => 'poll-cmd-key',
            'client_id' => 'client-uuid-2',
        ]);

        $command = \App\Models\BridgeCommand::create([
            'location_id' => $bridge->location_id,
            'command'     => 'restart',
            'payload'     => null,
            'status'      => 'pending',
        ]);

        $response = $this->getJson(
            '/api/bridges/commands/client-uuid-2',
            ['Authorization' => 'Bearer poll-cmd-key']
        );

        $response->assertStatus(200);
        $response->assertJson([
            'commandId' => $command->id,
            'command'   => 'restart',
            'payload'   => null,
        ]);

        $this->assertDatabaseHas('bridge_commands', [
            'id'     => $command->id,
            'status' => 'sent',
        ]);
    }

    public function test_poll_commands_returns_403_when_client_id_mismatch(): void
    {
        $bridge = LocationBridge::factory()->create([
            'api_key'   => 'poll-mismatch-key',
            'client_id' => 'real-client-uuid',
        ]);

        $response = $this->getJson(
            '/api/bridges/commands/wrong-client-uuid',
            ['Authorization' => 'Bearer poll-mismatch-key']
        );

        $response->assertStatus(403);
    }
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_poll_commands"
```

Expected: FAIL.

- [ ] **Step 3: Implement pollCommands() in BridgeController**

Replace the `pollCommands()` stub method:

```php
public function pollCommands(Request $request, string $clientId): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
{
    $bridge = $request->attributes->get('bridge');

    if ($bridge->client_id !== $clientId) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $command = \App\Models\BridgeCommand::where('location_id', $bridge->location_id)
        ->where('status', 'pending')
        ->orderBy('created_at')
        ->first();

    if (!$command) {
        return response()->noContent();
    }

    $command->update(['status' => 'sent']);

    return response()->json([
        'commandId' => $command->id,
        'command'   => $command->command,
        'payload'   => $command->payload,
    ]);
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_poll_commands"
```

Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/BridgeController.php tests/Feature/BridgeApiTest.php
git commit -m "feat: implement bridge poll commands endpoint"
```

---

## Task 8: Ack Command Endpoint

**Files:**
- Modify: `app/Http/Controllers/Api/BridgeController.php`
- Test: `tests/Feature/BridgeApiTest.php` (append)

- [ ] **Step 1: Write failing tests for ackCommand**

Append to `tests/Feature/BridgeApiTest.php`:

```php
    // ── Ack Command ───────────────────────────────────────────────────────────

    public function test_ack_marks_command_completed_on_success(): void
    {
        $bridge = LocationBridge::factory()->create([
            'api_key'   => 'ack-success-key',
            'client_id' => 'client-uuid-ack',
        ]);

        $command = \App\Models\BridgeCommand::create([
            'location_id' => $bridge->location_id,
            'command'     => 'restart',
            'status'      => 'sent',
        ]);

        $response = $this->postJson(
            '/api/bridges/commands/client-uuid-ack/ack',
            ['commandId' => $command->id, 'success' => true, 'message' => 'Restarting...'],
            ['Authorization' => 'Bearer ack-success-key']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('bridge_commands', [
            'id'          => $command->id,
            'status'      => 'completed',
            'ack_message' => 'Restarting...',
        ]);
    }

    public function test_ack_marks_command_failed_on_failure(): void
    {
        $bridge = LocationBridge::factory()->create([
            'api_key'   => 'ack-fail-key',
            'client_id' => 'client-uuid-ack2',
        ]);

        $command = \App\Models\BridgeCommand::create([
            'location_id' => $bridge->location_id,
            'command'     => 'restart',
            'status'      => 'sent',
        ]);

        $this->postJson(
            '/api/bridges/commands/client-uuid-ack2/ack',
            ['commandId' => $command->id, 'success' => false, 'message' => 'Failed to restart'],
            ['Authorization' => 'Bearer ack-fail-key']
        );

        $this->assertDatabaseHas('bridge_commands', [
            'id'     => $command->id,
            'status' => 'failed',
        ]);
    }

    public function test_ack_returns_404_when_command_belongs_to_different_location(): void
    {
        $bridge1 = LocationBridge::factory()->create(['api_key' => 'ack-loc1-key', 'client_id' => 'c1']);
        $bridge2 = LocationBridge::factory()->create(['api_key' => 'ack-loc2-key', 'client_id' => 'c2']);

        $commandForBridge2 = \App\Models\BridgeCommand::create([
            'location_id' => $bridge2->location_id,
            'command'     => 'restart',
            'status'      => 'sent',
        ]);

        $response = $this->postJson(
            '/api/bridges/commands/c1/ack',
            ['commandId' => $commandForBridge2->id, 'success' => true, 'message' => 'ok'],
            ['Authorization' => 'Bearer ack-loc1-key']
        );

        $response->assertStatus(404);
    }
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/BridgeApiTest.php --filter="test_ack"
```

Expected: FAIL.

- [ ] **Step 3: Implement ackCommand() in BridgeController**

Replace the `ackCommand()` stub method:

```php
public function ackCommand(Request $request, string $clientId): \Illuminate\Http\JsonResponse
{
    $bridge = $request->attributes->get('bridge');

    $data = $request->validate([
        'commandId' => 'required|string',
        'success'   => 'required|boolean',
        'message'   => 'nullable|string',
    ]);

    $command = \App\Models\BridgeCommand::where('id', $data['commandId'])
        ->where('location_id', $bridge->location_id)
        ->first();

    if (!$command) {
        return response()->json(['message' => 'Not found'], 404);
    }

    $command->update([
        'status'      => $data['success'] ? 'completed' : 'failed',
        'ack_message' => $data['message'] ?? null,
    ]);

    return response()->json(['ok' => true]);
}
```

- [ ] **Step 4: Run all BridgeApiTest tests**

```bash
php artisan test tests/Feature/BridgeApiTest.php
```

Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/BridgeController.php tests/Feature/BridgeApiTest.php
git commit -m "feat: implement bridge ack command endpoint"
```

---

## Task 9: MarkBridgesOffline Command

**Files:**
- Create: `app/Console/Commands/MarkBridgesOffline.php`
- Modify: `routes/console.php`
- Test: `tests/Unit/MarkBridgesOfflineTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Unit/MarkBridgesOfflineTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\LocationBridge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkBridgesOfflineTest extends TestCase
{
    public function test_marks_online_bridges_offline_when_last_seen_exceeds_90_seconds(): void
    {
        $stale = LocationBridge::factory()->create([
            'status'       => 'online',
            'last_seen_at' => now()->subSeconds(91),
        ]);

        $fresh = LocationBridge::factory()->create([
            'status'       => 'online',
            'last_seen_at' => now()->subSeconds(30),
        ]);

        $this->artisan('bridges:mark-offline');

        $this->assertEquals('offline', $stale->fresh()->status);
        $this->assertEquals('online', $fresh->fresh()->status);
    }

    public function test_does_not_touch_never_connected_bridges(): void
    {
        $bridge = LocationBridge::factory()->create([
            'status'       => 'never_connected',
            'last_seen_at' => null,
        ]);

        $this->artisan('bridges:mark-offline');

        $this->assertEquals('never_connected', $bridge->fresh()->status);
    }

    public function test_does_not_touch_already_offline_bridges(): void
    {
        $bridge = LocationBridge::factory()->create([
            'status'       => 'offline',
            'last_seen_at' => now()->subMinutes(10),
        ]);

        $this->artisan('bridges:mark-offline');

        // No error, no change — still offline
        $this->assertEquals('offline', $bridge->fresh()->status);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Unit/MarkBridgesOfflineTest.php
```

Expected: FAIL — command does not exist.

- [ ] **Step 3: Create MarkBridgesOffline command**

```php
<?php
// app/Console/Commands/MarkBridgesOffline.php

namespace App\Console\Commands;

use App\Models\LocationBridge;
use Illuminate\Console\Command;

class MarkBridgesOffline extends Command
{
    protected $signature = 'bridges:mark-offline';
    protected $description = 'Mark bridge connections as offline if last_seen_at is older than 90 seconds';

    public function handle(): void
    {
        $cutoff = now()->subSeconds(90);

        LocationBridge::where('status', '!=', 'offline')
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '<', $cutoff)
            ->update(['status' => 'offline']);
    }
}
```

- [ ] **Step 4: Register schedule in routes/console.php**

Add to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('bridges:mark-offline')->everyMinute();
```

- [ ] **Step 5: Run tests**

```bash
php artisan test tests/Unit/MarkBridgesOfflineTest.php
```

Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/MarkBridgesOffline.php routes/console.php \
        tests/Unit/MarkBridgesOfflineTest.php
git commit -m "feat: add bridges:mark-offline artisan command with schedule"
```

---

## Task 10: LocationBridgeController (Web Admin Actions)

**Files:**
- Create: `app/Http/Controllers/LocationBridgeController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/LocationBridgeAdminTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/LocationBridgeAdminTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\BridgeCommand;
use App\Models\Company;
use App\Models\Location;
use App\Models\LocationBridge;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class LocationBridgeAdminTest extends TestCase
{
    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', 'SUPER_ADMIN')->first()->id,
            'status'  => 'active',
        ]);
    }

    private function makeCompanyAdminWithLocation(): array
    {
        $role     = Role::where('name', 'COMPANY_ADMIN')->first();
        $company  = Company::factory()->create();
        $admin    = User::factory()->create(['role_id' => $role->id, 'company_id' => $company->id, 'status' => 'active']);
        $location = Location::factory()->create(['company_id' => $company->id]);
        return [$admin, $location];
    }

    // ── Generate API Key ──────────────────────────────────────────────────────

    public function test_super_admin_can_generate_api_key_for_location(): void
    {
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/generate-key");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('location_bridges', [
            'location_id' => $location->id,
        ]);

        $bridge = LocationBridge::where('location_id', $location->id)->first();
        $this->assertNotNull($bridge->api_key);
        $this->assertEquals(64, strlen($bridge->api_key));
    }

    public function test_company_admin_can_generate_api_key_for_own_location(): void
    {
        [$admin, $location] = $this->makeCompanyAdminWithLocation();

        $response = $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/generate-key");

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_company_admin_cannot_generate_api_key_for_other_company_location(): void
    {
        [$admin, ] = $this->makeCompanyAdminWithLocation();
        $otherLocation = Location::factory()->create(); // different company

        $response = $this->actingAs($admin)
            ->post("/locations/{$otherLocation->slug}/bridge/generate-key");

        $response->assertStatus(403);
    }

    public function test_generate_key_overwrites_existing_key(): void
    {
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create();
        $bridge   = LocationBridge::factory()->create([
            'location_id' => $location->id,
            'api_key'     => 'old-key-value',
        ]);

        $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/generate-key");

        $bridge->refresh();
        $this->assertNotEquals('old-key-value', $bridge->api_key);
    }

    // ── Create Command ────────────────────────────────────────────────────────

    public function test_super_admin_can_create_restart_command(): void
    {
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create();
        LocationBridge::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/commands", [
                'command' => 'restart',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bridge_commands', [
            'location_id' => $location->id,
            'command'     => 'restart',
            'status'      => 'pending',
        ]);
    }

    public function test_can_create_set_config_command_with_payload(): void
    {
        $admin    = $this->makeSuperAdmin();
        $location = Location::factory()->create();
        LocationBridge::factory()->create(['location_id' => $location->id]);

        $this->actingAs($admin)
            ->post("/locations/{$location->slug}/bridge/commands", [
                'command' => 'set_config',
                'payload' => ['BRIDGE_MODE' => 'test'],
            ]);

        $this->assertDatabaseHas('bridge_commands', [
            'location_id' => $location->id,
            'command'     => 'set_config',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_command(): void
    {
        $location = Location::factory()->create();

        $response = $this->post("/locations/{$location->slug}/bridge/commands", [
            'command' => 'restart',
        ]);

        $response->assertRedirect('/login');
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/LocationBridgeAdminTest.php
```

Expected: FAIL — routes don't exist.

- [ ] **Step 3: Create LocationBridgeController**

```php
<?php
// app/Http/Controllers/LocationBridgeController.php

namespace App\Http\Controllers;

use App\Models\BridgeCommand;
use App\Models\Location;
use App\Models\LocationBridge;
use Illuminate\Http\Request;

class LocationBridgeController extends Controller
{
    public function generateKey(Location $location)
    {
        $this->authorize('update', $location);

        $newKey = bin2hex(random_bytes(32));

        LocationBridge::updateOrCreate(
            ['location_id' => $location->id],
            ['api_key' => $newKey]
        );

        return redirect()->back()->with('success', 'API Key generat cu succes.');
    }

    public function createCommand(Request $request, Location $location)
    {
        $this->authorize('update', $location);

        $validated = $request->validate([
            'command' => 'required|in:restart,set_config',
            'payload' => 'nullable|array',
        ]);

        BridgeCommand::create([
            'location_id' => $location->id,
            'command'     => $validated['command'],
            'payload'     => $validated['payload'] ?? null,
            'status'      => 'pending',
        ]);

        return redirect()->back()->with('success', 'Comandă trimisă cu succes.');
    }
}
```

- [ ] **Step 4: Add web routes**

In `routes/web.php`, add inside the `auth` middleware group (find the appropriate place near location routes):

```php
use App\Http\Controllers\LocationBridgeController;

Route::middleware('auth')->group(function () {
    // ... existing routes ...
    Route::post('/locations/{location}/bridge/generate-key', [LocationBridgeController::class, 'generateKey'])
        ->name('locations.bridge.generate-key');
    Route::post('/locations/{location}/bridge/commands', [LocationBridgeController::class, 'createCommand'])
        ->name('locations.bridge.commands');
});
```

- [ ] **Step 5: Run tests**

```bash
php artisan test tests/Feature/LocationBridgeAdminTest.php
```

Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/LocationBridgeController.php \
        routes/web.php \
        tests/Feature/LocationBridgeAdminTest.php
git commit -m "feat: add LocationBridgeController for admin key generation and commands"
```

---

## Task 11: Admin UI — "Configurare Bridge" section

**Files:**
- Modify: `resources/views/locations/edit.blade.php`

- [ ] **Step 1: Pass bridge and logs data from LocationController to edit view**

In `app/Http/Controllers/LocationController.php`, update the `edit()` method to pass bridge data:

```php
public function edit(Location $location)
{
    $this->authorize('update', $location);

    $user = Auth::user();
    $companies = null;
    if ($user->isSuperAdmin()) {
        $companies = Company::orderBy('name')->get();
    }

    $bridge = $location->bridge;
    $recentLogs = $bridge
        ? \App\Models\BridgeLog::where('location_id', $location->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
        : collect();

    return view('locations.edit', compact('location', 'companies', 'bridge', 'recentLogs'));
}
```

- [ ] **Step 2: Add "Configurare Bridge" section to edit.blade.php**

At the bottom of `resources/views/locations/edit.blade.php`, before the closing `</div>` of the main content section, add:

```blade
{{-- ── Configurare Bridge ─────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6"
     x-data="{
         showKey: false,
         apiKey: '{{ $bridge?->api_key ?? '' }}',
         confirmRegenerate: false,
         async generateKey() {
             if (this.apiKey && !this.confirmRegenerate) {
                 this.confirmRegenerate = true;
                 return;
             }
             this.confirmRegenerate = false;
             const res = await fetch('{{ route('locations.bridge.generate-key', $location) }}', {
                 method: 'POST',
                 headers: {
                     'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                     'Accept': 'application/json',
                 },
             });
             if (res.ok) {
                 const data = await res.json();
                 this.apiKey = data.api_key;
                 this.showKey = true;
             }
         }
     }">

    <h2 class="text-xl font-semibold text-gray-900 mb-6">
        <i class="fas fa-plug mr-2 text-indigo-500"></i>Configurare Bridge
    </h2>

    {{-- API Key --}}
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
        <div class="flex items-center gap-3">
            <input type="text"
                   :value="showKey ? apiKey : (apiKey ? '••••••••••••••••' : 'Negenerată')"
                   readonly
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm">
            <button type="button"
                    @click="showKey = !showKey"
                    x-show="apiKey"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-100">
                <span x-text="showKey ? 'Ascunde' : 'Arată'"></span>
            </button>
        </div>

        {{-- Confirm regenerate warning --}}
        <div x-show="confirmRegenerate" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
            <strong>Atenție:</strong> Bridge-ul existent va trebui reconfigurat manual cu noul key.
            <button type="button" @click="generateKey()"
                    class="ml-3 px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-xs">
                Confirmă regenerarea
            </button>
            <button type="button" @click="confirmRegenerate = false"
                    class="ml-2 px-3 py-1 border border-yellow-400 rounded text-xs hover:bg-yellow-100">
                Anulează
            </button>
        </div>

        <button type="button"
                @click="generateKey()"
                x-show="!confirmRegenerate"
                class="mt-3 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
            <i class="fas fa-key mr-2"></i>
            <span x-text="apiKey ? 'Regenerează API Key' : 'Generează API Key'"></span>
        </button>
    </div>

    {{-- Bridge Status (only if bridge record exists) --}}
    @if($bridge)
    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <div class="text-xs text-gray-500 mb-1">Status</div>
            @if($bridge->status === 'online')
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span> Online
                </span>
            @elseif($bridge->status === 'offline')
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span> Offline
                </span>
            @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                    <span class="w-2 h-2 rounded-full bg-gray-400 mr-1"></span> Neconfigurat
                </span>
            @endif
        </div>
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <div class="text-xs text-gray-500 mb-1">Ultima activitate</div>
            <div class="text-sm font-medium">{{ $bridge->last_seen_at?->format('d.m.Y H:i:s') ?? '—' }}</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <div class="text-xs text-gray-500 mb-1">Versiune / Mod</div>
            <div class="text-sm font-medium">{{ $bridge->version ?? '—' }} / {{ $bridge->mode ?? '—' }}</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <div class="text-xs text-gray-500 mb-1">Bonuri / Z / Erori</div>
            <div class="text-sm font-medium">{{ $bridge->print_count }} / {{ $bridge->z_report_count }} / {{ $bridge->error_count }}</div>
        </div>
    </div>

    {{-- Quick Commands --}}
    <div class="mb-6">
        <h3 class="text-sm font-medium text-gray-700 mb-3">Comenzi rapide</h3>
        <div class="flex gap-3 flex-wrap">
            <form method="POST" action="{{ route('locations.bridge.commands', $location) }}">
                @csrf
                <input type="hidden" name="command" value="restart">
                <button type="submit"
                        class="px-4 py-2 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600"
                        onclick="return confirm('Trimiți comandă restart bridge?')">
                    <i class="fas fa-redo mr-2"></i>Restart bridge
                </button>
            </form>
            <form method="POST" action="{{ route('locations.bridge.commands', $location) }}">
                @csrf
                <input type="hidden" name="command" value="set_config">
                <input type="hidden" name="payload[BRIDGE_MODE]" value="test">
                <button type="submit" class="px-4 py-2 bg-yellow-500 text-white text-sm rounded-lg hover:bg-yellow-600">
                    <i class="fas fa-flask mr-2"></i>Mod test
                </button>
            </form>
            <form method="POST" action="{{ route('locations.bridge.commands', $location) }}">
                @csrf
                <input type="hidden" name="command" value="set_config">
                <input type="hidden" name="payload[BRIDGE_MODE]" value="live">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                    <i class="fas fa-check-circle mr-2"></i>Mod live
                </button>
            </form>
        </div>
    </div>

    {{-- Recent Logs --}}
    @if($recentLogs->isNotEmpty())
    <div>
        <h3 class="text-sm font-medium text-gray-700 mb-3">Loguri recente (ultimele 50)</h3>
        <div class="overflow-auto max-h-64 border border-gray-200 rounded-lg">
            <table class="min-w-full text-xs font-mono">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-500">Timestamp</th>
                        <th class="px-3 py-2 text-left text-gray-500">Level</th>
                        <th class="px-3 py-2 text-left text-gray-500">Mesaj</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-1.5 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d.m H:i:s') }}</td>
                        <td class="px-3 py-1.5">
                            @if($log->level === 'error')
                                <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-700">error</span>
                            @elseif($log->level === 'warn')
                                <span class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700">warn</span>
                            @else
                                <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">info</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 text-gray-800">{{ $log->message }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif
</div>
```

- [ ] **Step 3: Update generateKey controller to return JSON for fetch calls**

In `LocationBridgeController::generateKey()`, detect if request expects JSON and return accordingly:

```php
public function generateKey(Location $location)
{
    $this->authorize('update', $location);

    $newKey = bin2hex(random_bytes(32));

    LocationBridge::updateOrCreate(
        ['location_id' => $location->id],
        ['api_key' => $newKey]
    );

    if (request()->expectsJson()) {
        return response()->json(['api_key' => $newKey]);
    }

    return redirect()->back()->with('success', 'API Key generat cu succes.');
}
```

- [ ] **Step 4: Manually verify the UI in browser**

1. Go to a location's edit page: `/locations/{slug}/edit`
2. Verify the "Configurare Bridge" card appears at the bottom
3. Click "Generează API Key" — key should appear in the input
4. Click again — confirmation warning should appear
5. Confirm — a new key should replace the old one

- [ ] **Step 5: Commit**

```bash
git add resources/views/locations/edit.blade.php \
        app/Http/Controllers/LocationController.php \
        app/Http/Controllers/LocationBridgeController.php
git commit -m "feat: add Configurare Bridge admin UI section to location edit page"
```

---

## Task 12: Full Test Run & Final Verification

- [ ] **Step 1: Run all tests**

```bash
php artisan test
```

Expected: all tests pass, no regressions.

- [ ] **Step 2: Check for any leftover bridge_config references**

```bash
grep -rn "bridge_config" app/ resources/ routes/ database/
```

Expected: no output (or only migration down() rollback references).

- [ ] **Step 3: Final commit if any cleanup needed**

```bash
git add -A
git commit -m "chore: final cleanup and bridge integration complete"
```

---

## Quick Reference

| What | Command |
|------|---------|
| Run all tests | `php artisan test` |
| Run bridge API tests | `php artisan test tests/Feature/BridgeApiTest.php` |
| Run admin tests | `php artisan test tests/Feature/LocationBridgeAdminTest.php` |
| Run offline command tests | `php artisan test tests/Unit/MarkBridgesOfflineTest.php` |
| Run migrations | `php artisan migrate` |
| Run offline command manually | `php artisan bridges:mark-offline` |
