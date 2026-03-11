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
        Schema::create('standalone_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standalone_receipt_id')->constrained('standalone_receipts')->onDelete('cascade');
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id');
            $table->string('name');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->index(['standalone_receipt_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standalone_receipt_items');
    }
};
