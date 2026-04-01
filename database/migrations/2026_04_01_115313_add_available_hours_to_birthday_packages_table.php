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
        Schema::table('birthday_packages', function (Blueprint $table) {
            $table->time('available_from')->nullable()->after('duration_minutes');
            $table->time('available_until')->nullable()->after('available_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('birthday_packages', function (Blueprint $table) {
            $table->dropColumn(['available_from', 'available_until']);
        });
    }
};
