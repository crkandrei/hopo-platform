<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode', 100)->nullable()->after('has_sgr');
            $table->unique(['location_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['location_id', 'barcode']);
            $table->dropColumn('barcode');
        });
    }
};
