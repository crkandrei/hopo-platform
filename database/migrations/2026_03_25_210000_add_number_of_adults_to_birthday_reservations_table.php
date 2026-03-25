<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->unsignedSmallInteger('number_of_adults')->nullable()->after('number_of_children');
        });
    }

    public function down(): void
    {
        Schema::table('birthday_reservations', function (Blueprint $table) {
            $table->dropColumn('number_of_adults');
        });
    }
};
