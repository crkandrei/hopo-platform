<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standalone_receipts', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->after('total_amount')->constrained('vouchers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('standalone_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('standalone_receipts', 'voucher_id')) {
                $table->dropForeign(['voucher_id']);
                $table->dropColumn('voucher_id');
            }
        });
    }
};
