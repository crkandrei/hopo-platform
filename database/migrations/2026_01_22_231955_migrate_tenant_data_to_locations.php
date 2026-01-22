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
        // Create temporary mapping table
        Schema::create('tenant_location_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('location_id');
            $table->timestamps();
        });
        
        // Migrate existing tenants to companies + locations
        $tenants = DB::table('tenants')->get();
        
        foreach ($tenants as $tenant) {
            // 1. Create Company from Tenant
            $companyId = DB::table('companies')->insertGetId([
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'is_active' => $tenant->is_active,
                'created_at' => $tenant->created_at,
                'updated_at' => $tenant->updated_at,
            ]);
            
            // 2. Create Location from Tenant
            $locationId = DB::table('locations')->insertGetId([
                'company_id' => $companyId,
                'name' => $tenant->name,
                'slug' => 'main',
                'address' => $tenant->address,
                'phone' => $tenant->phone,
                'email' => $tenant->email,
                'price_per_hour' => $tenant->price_per_hour ?? 0.00,
                'is_active' => true,
                'created_at' => $tenant->created_at,
                'updated_at' => $tenant->updated_at,
            ]);
            
            // 3. Store mapping for later use in other migrations
            DB::table('tenant_location_mapping')->insert([
                'tenant_id' => $tenant->id,
                'company_id' => $companyId,
                'location_id' => $locationId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop mapping table
        Schema::dropIfExists('tenant_location_mapping');
        
        // Note: We don't reverse the company/location creation
        // as this would require complex data reconstruction
    }
};
