<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('play_session_id')->nullable()->constrained('play_sessions')->onDelete('set null');
            $table->foreignId('standalone_receipt_id')->nullable()->constrained('standalone_receipts')->onDelete('set null');
            $table->decimal('amount_used', 8, 2)->nullable();
            $table->decimal('hours_used', 8, 2)->nullable();
            $table->dateTime('used_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('voucher_id');
            $table->index('play_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
    }
};
