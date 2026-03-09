<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('special_period_rates', function (Blueprint $table) {
            $table->string('pricing_mode')->default('flat_hourly')->after('hourly_rate');
            $table->decimal('price_1h', 10, 2)->nullable()->after('pricing_mode');
            $table->decimal('price_2h', 10, 2)->nullable()->after('price_1h');
            $table->decimal('price_3h', 10, 2)->nullable()->after('price_2h');
            $table->decimal('price_4h', 10, 2)->nullable()->after('price_3h');
            $table->decimal('overflow_price_per_hour', 10, 2)->nullable()->after('price_4h');
        });
    }

    public function down(): void
    {
        Schema::table('special_period_rates', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_mode',
                'price_1h',
                'price_2h',
                'price_3h',
                'price_4h',
                'overflow_price_per_hour',
            ]);
        });
    }
};
