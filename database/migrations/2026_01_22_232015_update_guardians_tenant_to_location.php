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
        Schema::table('guardians', function (Blueprint $table) {
            // Add location_id column
            $table->foreignId('location_id')->nullable()->after('id');
        });
        
        // Migrate tenant_id to location_id using mapping
        $mappings = DB::table('tenant_location_mapping')->get();
        
        foreach ($mappings as $mapping) {
            DB::statement("UPDATE guardians SET location_id = {$mapping->location_id} WHERE tenant_id = {$mapping->tenant_id}");
        }
        
        Schema::table('guardians', function (Blueprint $table) {
            // Make location_id required and add foreign key
            $table->foreignId('location_id')->nullable(false)->change();
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            
            // Drop old tenant_id
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            // Add tenant_id back
            $table->foreignId('tenant_id')->nullable()->after('id');
        });
        
        // Reverse migration
        $mappings = DB::table('tenant_location_mapping')->get();
        
        foreach ($mappings as $mapping) {
            DB::statement("UPDATE guardians SET tenant_id = {$mapping->tenant_id} WHERE location_id = {$mapping->location_id}");
        }
        
        Schema::table('guardians', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};
