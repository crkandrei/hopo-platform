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
        Schema::table('users', function (Blueprint $table) {
            // Add company_id column
            $table->foreignId('company_id')->nullable()->after('id');
        });
        
        // Migrate tenant_id to location_id and company_id
        // First, get mapping from tenant_location_mapping
        $mappings = DB::table('tenant_location_mapping')->get();
        
        foreach ($mappings as $mapping) {
            // Update users: set location_id from tenant_id, and company_id for COMPANY_ADMIN
            DB::table('users')
                ->where('tenant_id', $mapping->tenant_id)
                ->update([
                    'location_id' => $mapping->location_id,
                    'company_id' => DB::raw("CASE 
                        WHEN role_id = (SELECT id FROM roles WHERE name = 'COMPANY_ADMIN') 
                        THEN {$mapping->company_id} 
                        ELSE NULL 
                    END"),
                ]);
        }
        
        Schema::table('users', function (Blueprint $table) {
            // Rename tenant_id to location_id
            $table->renameColumn('tenant_id', 'location_id');
            
            // Add foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['company_id']);
            $table->dropForeign(['location_id']);
            
            // Rename location_id back to tenant_id
            $table->renameColumn('location_id', 'tenant_id');
            
            // Drop company_id
            $table->dropColumn('company_id');
        });
        
        // Reverse migration: restore tenant_id from location_id using mapping
        $mappings = DB::table('tenant_location_mapping')->get();
        
        foreach ($mappings as $mapping) {
            DB::table('users')
                ->where('location_id', $mapping->location_id)
                ->update([
                    'tenant_id' => $mapping->tenant_id,
                ]);
        }
    }
};
