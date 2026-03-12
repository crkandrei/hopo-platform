<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birthday_packages', function (Blueprint $table) {
            $table->dropColumn(['price', 'max_children']);
        });

        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->dropColumn('total_price');
        });
    }

    public function down(): void
    {
        Schema::table('birthday_packages', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(0)->after('description');
            $table->integer('max_children')->default(20)->after('includes_decorations');
        });

        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->decimal('total_price', 8, 2)->default(0)->after('notes');
        });
    }
};
