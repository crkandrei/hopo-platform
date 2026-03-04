<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Create birthday_reservations with new schema if missing; otherwise run
     * phased migration (add nullable columns, backfill, drop legacy columns, enforce constraints).
     */
    public function up(): void
    {
        if (!Schema::hasTable('birthday_reservations')) {
            Schema::create('birthday_reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
                $table->foreignId('birthday_hall_id')->constrained('birthday_halls')->onDelete('cascade');
                $table->foreignId('birthday_package_id')->constrained('birthday_packages')->onDelete('cascade');
                $table->foreignId('time_slot_id')->nullable()->constrained('birthday_time_slots')->onDelete('set null');
                $table->date('reservation_date');
                $table->time('reservation_time')->nullable();
                $table->string('child_name');
                $table->integer('child_age')->nullable();
                $table->string('guardian_name');
                $table->string('guardian_phone');
                $table->string('guardian_email')->nullable();
                $table->integer('number_of_children')->default(1);
                $table->text('notes')->nullable();
                $table->decimal('total_price', 8, 2);
                $table->string('status')->default('pending');
                $table->string('token')->unique();
                $table->timestamps();

                $table->index('location_id');
                $table->index('birthday_hall_id');
                $table->index('reservation_date');
                $table->index('status');
                $table->index('token');
            });
            return;
        }

        // --- Phase 1: Add new columns as nullable (keep tenant_id, created_by for backfill) ---
        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('id');
            $table->unsignedBigInteger('birthday_hall_id')->nullable()->after('location_id');
            $table->unsignedBigInteger('birthday_package_id')->nullable()->after('birthday_hall_id');
            $table->unsignedBigInteger('time_slot_id')->nullable()->after('birthday_package_id');
            $table->string('guardian_name')->nullable()->after('child_name');
            $table->integer('child_age')->nullable()->after('child_name');
            $table->string('guardian_email')->nullable()->after('guardian_phone');
            $table->decimal('total_price', 8, 2)->nullable()->after('notes');
            $table->string('status')->default('pending')->after('total_price');
            $table->string('token')->nullable()->after('status');
        });

        // --- Phase 2: Backfill existing rows ---
        $tableName = 'birthday_reservations';

        // Backfill unique token for every row
        foreach (DB::table($tableName)->whereNull('token')->get() as $row) {
            DB::table($tableName)->where('id', $row->id)->update(['token' => (string) Str::uuid()]);
        }

        // Backfill location_id from created_by user's location_id (when available)
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('
                UPDATE birthday_reservations r
                INNER JOIN users u ON u.id = r.created_by AND u.location_id IS NOT NULL
                SET r.location_id = u.location_id
                WHERE r.location_id IS NULL
            ');
        } else {
            foreach (DB::table('users')->whereNotNull('location_id')->get(['id', 'location_id']) as $user) {
                DB::table($tableName)
                    ->where('created_by', $user->id)
                    ->whereNull('location_id')
                    ->update(['location_id' => $user->location_id]);
            }
        }

        // Backfill location_id for rows that have created_by but user may have null location_id:
        // use first location that has at least one hall and one package (so we can backfill hall/package too)
        $defaultLocationId = DB::table('locations')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('birthday_halls')
                    ->whereColumn('birthday_halls.location_id', 'locations.id');
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('birthday_packages')
                    ->whereColumn('birthday_packages.location_id', 'locations.id');
            })
            ->value('id');

        if ($defaultLocationId !== null) {
            DB::table($tableName)->whereNull('location_id')->update(['location_id' => $defaultLocationId]);
        }

        // Backfill birthday_hall_id: first hall for each row's location_id
        foreach (DB::table($tableName)->whereNotNull('location_id')->whereNull('birthday_hall_id')->get(['id', 'location_id']) as $row) {
            $hallId = DB::table('birthday_halls')->where('location_id', $row->location_id)->value('id');
            if ($hallId !== null) {
                DB::table($tableName)->where('id', $row->id)->update(['birthday_hall_id' => $hallId]);
            }
        }

        // Backfill birthday_package_id: first package for each row's location_id
        foreach (DB::table($tableName)->whereNotNull('location_id')->whereNull('birthday_package_id')->get(['id', 'location_id']) as $row) {
            $packageId = DB::table('birthday_packages')->where('location_id', $row->location_id)->value('id');
            if ($packageId !== null) {
                DB::table($tableName)->where('id', $row->id)->update(['birthday_package_id' => $packageId]);
            }
        }

        // Backfill guardian_name (use child_name or empty string for legacy)
        DB::table($tableName)
            ->whereNull('guardian_name')
            ->update(['guardian_name' => DB::raw('COALESCE(child_name, \'\')')]);

        // Backfill total_price for legacy rows
        DB::table($tableName)->whereNull('total_price')->update(['total_price' => 0]);

        // Migration strategy: remove rows that cannot satisfy NOT NULL constraints (no location, hall, or package).
        // Export or archive such rows before running this migration if you need to preserve them.
        DB::table($tableName)
            ->whereNull('location_id')
            ->orWhereNull('birthday_hall_id')
            ->orWhereNull('birthday_package_id')
            ->orWhereNull('token')
            ->orWhereNull('guardian_name')
            ->delete();

        // --- Phase 3: Drop old columns ---
        Schema::table('birthday_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('birthday_reservations', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
            if (Schema::hasColumn('birthday_reservations', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        // --- Phase 4: Enforce non-null and constraints ---
        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable(false)->change();
            $table->unsignedBigInteger('birthday_hall_id')->nullable(false)->change();
            $table->unsignedBigInteger('birthday_package_id')->nullable(false)->change();
            $table->string('guardian_name')->nullable(false)->change();
            $table->decimal('total_price', 8, 2)->nullable(false)->change();
            $table->string('token')->nullable(false)->change();
        });

        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('birthday_hall_id')->references('id')->on('birthday_halls')->onDelete('cascade');
            $table->foreign('birthday_package_id')->references('id')->on('birthday_packages')->onDelete('cascade');
            $table->foreign('time_slot_id')->references('id')->on('birthday_time_slots')->onDelete('set null');
            $table->unique('token');
        });

        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->time('reservation_time')->nullable()->change();
        });

        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->index('location_id');
            $table->index('birthday_hall_id');
            $table->index('reservation_date');
            $table->index('status');
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->dropIndex(['location_id']);
            $table->dropIndex(['birthday_hall_id']);
            $table->dropIndex(['reservation_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['token']);
        });

        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropForeign(['time_slot_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['birthday_hall_id']);
            $table->dropForeign(['birthday_package_id']);
            $table->dropColumn([
                'location_id', 'birthday_hall_id', 'birthday_package_id', 'time_slot_id',
                'guardian_name', 'child_age', 'guardian_email', 'total_price', 'status', 'token',
            ]);
        });

        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->time('reservation_time')->nullable(false)->change();
        });

        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        });
    }
};
