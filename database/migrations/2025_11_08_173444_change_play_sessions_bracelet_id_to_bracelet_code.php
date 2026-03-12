<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add bracelet_code column first (nullable for now) - only if it doesn't exist
        if (!Schema::hasColumn('play_sessions', 'bracelet_code')) {
            Schema::table('play_sessions', function (Blueprint $table) {
                $table->string('bracelet_code', 50)->nullable()->after('child_id');
            });
        }

        // Migrate existing bracelet_id values to bracelet_code (only if bracelet_id still exists)
        if (Schema::hasColumn('play_sessions', 'bracelet_id')) {
            DB::statement('
                UPDATE play_sessions ps
                INNER JOIN bracelets b ON ps.bracelet_id = b.id
                SET ps.bracelet_code = b.code
                WHERE ps.bracelet_id IS NOT NULL AND ps.bracelet_code IS NULL
            ');
        }

        // Drop foreign key and bracelet_id column if they exist
        if (Schema::hasColumn('play_sessions', 'bracelet_id')) {
            Schema::table('play_sessions', function (Blueprint $table) {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'play_sessions'
                    AND COLUMN_NAME = 'bracelet_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['bracelet_id']);
                }
                $table->dropColumn('bracelet_id');
            });
        }

        // Add index on bracelet_code for performance (only if it doesn't exist)
        if (Schema::hasColumn('play_sessions', 'bracelet_code')) {
            $indexes = DB::select("SHOW INDEXES FROM play_sessions WHERE Key_name = 'play_sessions_bracelet_code_index'");
            if (empty($indexes)) {
                Schema::table('play_sessions', function (Blueprint $table) {
                    $table->index('bracelet_code');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('play_sessions', function (Blueprint $table) {
            // Add bracelet_id column back
            $table->foreignId('bracelet_id')->nullable()->after('child_id');
        });

        // Try to restore bracelet_id values from bracelet_code (if bracelets still exist)
        DB::statement('
            UPDATE play_sessions ps
            INNER JOIN bracelets b ON ps.bracelet_code = b.code
            SET ps.bracelet_id = b.id
            WHERE ps.bracelet_code IS NOT NULL
        ');

        Schema::table('play_sessions', function (Blueprint $table) {
            // Drop bracelet_code column
            $table->dropColumn('bracelet_code');
            
            // Drop index on bracelet_code
            try {
                $table->dropIndex('play_sessions_bracelet_code_index');
            } catch (\Throwable $e) {
                // Index might not exist, ignore
            }
            
            // Re-add foreign key constraint
            $table->foreign('bracelet_id')->references('id')->on('bracelets')->onDelete('cascade');
            
            // Re-add index on bracelet_id
            $table->index('bracelet_id');
        });
    }
};
