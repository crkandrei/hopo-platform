<?php
// database/migrations/2026_03_19_100000_create_location_bridges_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_bridges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('api_key', 64)->unique()->nullable();
            $table->string('client_id')->nullable();
            $table->enum('status', ['online', 'offline', 'never_connected'])->default('never_connected');
            $table->string('version')->nullable();
            $table->enum('mode', ['live', 'test'])->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_print_at')->nullable();
            $table->integer('print_count')->default(0);
            $table->integer('z_report_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->unsignedInteger('uptime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_bridges');
    }
};
