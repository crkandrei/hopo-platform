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
        Schema::table('locations', function (Blueprint $table) {
            $table->string('pricing_mode')->default('flat_hourly')->after('price_per_hour');
            $table->decimal('overflow_price_per_hour', 10, 2)->nullable()->after('pricing_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['pricing_mode', 'overflow_price_per_hour']);
        });
    }
};
