<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign key constraints first
        // Note: Some foreign keys may have already been dropped by previous migrations
        
        // Drop foreign key from scan_events if it exists
        if (Schema::hasTable('scan_events') && Schema::hasColumn('scan_events', 'bracelet_id')) {
            try {
                DB::statement('ALTER TABLE scan_events DROP FOREIGN KEY scan_events_bracelet_id_foreign');
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        // Drop foreign key from users if it exists (should have been dropped by update_users_table_for_multitenancy)
        // But renameColumn doesn't drop the old foreign key, so we need to drop it manually
        if (Schema::hasTable('users')) {
            try {
                DB::statement('ALTER TABLE users DROP FOREIGN KEY users_tenant_id_foreign');
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        // Drop deprecated tables in correct order (respecting foreign keys)
        Schema::dropIfExists('birthday_reservations');
        Schema::dropIfExists('bracelets');
        Schema::dropIfExists('tenant_configurations');
        
        // Drop main tenant table last (after all references are removed)
        Schema::dropIfExists('tenants');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration does not recreate the tables as they are deprecated
        // If rollback is needed, restore from a backup or recreate manually
    }
};
