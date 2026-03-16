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
        Schema::table('location_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('location_id');
            $table->enum('payment_source', ['manual', 'stripe'])->nullable()->after('payment_method');
            $table->string('stripe_session_id')->nullable()->after('payment_source');
            $table->string('stripe_payment_id')->nullable()->after('stripe_session_id');

            $table->foreign('plan_id')->references('id')->on('subscription_plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('location_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'payment_source', 'stripe_session_id', 'stripe_payment_id']);
        });
    }
};
