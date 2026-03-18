<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for high-frequency queries.
 *
 * play_sessions:
 *   - (location_id, ended_at)   → dashboard income, unpaid alerts, completed session queries
 *   - (location_id, started_at) → dashboard stats, reports, session range queries
 *
 * scan_events:
 *   - (code_used, location_id, expires_at) → validateCode (hot path: every bracelet scan)
 *   - (location_id, created_at)            → scan statistics queries
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('play_sessions', function (Blueprint $table) {
            $table->index(['location_id', 'ended_at'],   'idx_ps_location_ended');
            $table->index(['location_id', 'started_at'], 'idx_ps_location_started');
        });

        Schema::table('scan_events', function (Blueprint $table) {
            $table->index(['code_used', 'location_id', 'expires_at'], 'idx_se_code_location_expires');
            $table->index(['location_id', 'created_at'],              'idx_se_location_created');
        });
    }

    public function down(): void
    {
        Schema::table('play_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_ps_location_ended');
            $table->dropIndex('idx_ps_location_started');
        });

        Schema::table('scan_events', function (Blueprint $table) {
            $table->dropIndex('idx_se_code_location_expires');
            $table->dropIndex('idx_se_location_created');
        });
    }
};
