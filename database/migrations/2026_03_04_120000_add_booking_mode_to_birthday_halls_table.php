<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birthday_halls', function (Blueprint $table) {
            $table->string('booking_mode', 20)->default('slots')->after('is_active')
                ->comment('slots = slot-based; flexible = any hour (flexible-time)');
        });
    }

    public function down(): void
    {
        Schema::table('birthday_halls', function (Blueprint $table) {
            $table->dropColumn('booking_mode');
        });
    }
};
