<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_package_weekdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('birthday_package_id')->constrained('birthday_packages')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('0=Luni..6=Duminică');
            $table->timestamps();

            $table->unique(['birthday_package_id', 'day_of_week']);
            $table->index('day_of_week');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_package_weekdays');
    }
};
