<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->integer('duration_minutes');
            $table->boolean('includes_food')->default(false);
            $table->boolean('includes_decorations')->default(false);
            $table->integer('max_children')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('location_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_packages');
    }
};
