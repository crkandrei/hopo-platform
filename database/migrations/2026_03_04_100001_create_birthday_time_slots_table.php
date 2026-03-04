<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('birthday_hall_id')->constrained('birthday_halls')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Luni..6=Duminică, null=orice zi');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_reservations')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('birthday_hall_id');
            $table->index('day_of_week');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_time_slots');
    }
};
