<?php
// database/migrations/2026_03_19_100002_create_bridge_commands_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bridge_commands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->enum('command', ['restart', 'set_config']);
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'sent', 'completed', 'failed'])->default('pending');
            $table->string('ack_message')->nullable();
            $table->timestamps();

            $table->index(['location_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_commands');
    }
};
