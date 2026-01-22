<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('special_period_rates', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('id');
        });
        
        $mappings = DB::table('tenant_location_mapping')->get();
        foreach ($mappings as $mapping) {
            DB::statement("UPDATE special_period_rates SET location_id = {$mapping->location_id} WHERE tenant_id = {$mapping->tenant_id}");
        }
        
        Schema::table('special_period_rates', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable(false)->change();
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('special_period_rates', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id');
        });
        
        $mappings = DB::table('tenant_location_mapping')->get();
        foreach ($mappings as $mapping) {
            DB::statement("UPDATE special_period_rates SET tenant_id = {$mapping->tenant_id} WHERE location_id = {$mapping->location_id}");
        }
        
        Schema::table('special_period_rates', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};
