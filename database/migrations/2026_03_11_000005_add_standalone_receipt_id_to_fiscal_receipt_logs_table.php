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
        Schema::table('fiscal_receipt_logs', function (Blueprint $table) {
            $table->foreignId('standalone_receipt_id')->nullable()->after('play_session_ids')->constrained('standalone_receipts')->onDelete('set null');
        });
        DB::statement("ALTER TABLE fiscal_receipt_logs MODIFY COLUMN type ENUM('session', 'standalone', 'z_report') NOT NULL DEFAULT 'session'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiscal_receipt_logs', function (Blueprint $table) {
            $table->dropForeign(['standalone_receipt_id']);
        });
        DB::statement("ALTER TABLE fiscal_receipt_logs MODIFY COLUMN type ENUM('session', 'z_report') NOT NULL DEFAULT 'session'");
    }
};
