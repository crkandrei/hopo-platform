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
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->decimal('duration_hours', 5, 2);
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['location_id', 'day_of_week', 'duration_hours']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};
