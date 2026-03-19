<?php
// database/migrations/2026_03_19_100001_create_bridge_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bridge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['info', 'warn', 'error']);
            $table->text('message');
            $table->timestamp('timestamp');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['location_id', 'level', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_logs');
    }
};
