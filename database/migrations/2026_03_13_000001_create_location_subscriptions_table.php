<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->index('location_id');
            $table->string('plan_type')->default('standard');
            $table->datetime('starts_at');
            $table->datetime('expires_at');
            $table->decimal('price_paid', 10, 2)->nullable();
            $table->enum('payment_method', ['bank_transfer', 'cash', 'card', 'other'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_subscriptions');
    }
};
