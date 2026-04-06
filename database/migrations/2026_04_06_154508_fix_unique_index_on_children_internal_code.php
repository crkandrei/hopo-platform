<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            // Indexul vechi (tenant_id, internal_code) a rămas fără tenant_id după
            // migrația de la tenant la location, devenind efectiv UNIQUE(internal_code)
            // pe întregul tabel — incorect. Îl înlocuim cu UNIQUE(location_id, internal_code).
            $table->dropUnique('children_tenant_id_internal_code_unique');
            $table->unique(['location_id', 'internal_code'], 'children_location_id_internal_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropUnique('children_location_id_internal_code_unique');
            $table->unique(['internal_code'], 'children_tenant_id_internal_code_unique');
        });
    }
};
