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
        Schema::create('stripe_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id')->unique();
            $table->string('event_type');
            $table->enum('status', ['processed', 'failed', 'skipped']);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_logs');
    }
};
