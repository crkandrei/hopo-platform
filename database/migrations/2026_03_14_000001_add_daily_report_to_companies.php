<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('daily_report_enabled')->default(false)->after('is_active');
            $table->string('daily_report_email')->nullable()->after('daily_report_enabled');
            $table->timestamp('daily_report_last_sent_at')->nullable()->after('daily_report_email');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['daily_report_enabled', 'daily_report_email', 'daily_report_last_sent_at']);
        });
    }
};
