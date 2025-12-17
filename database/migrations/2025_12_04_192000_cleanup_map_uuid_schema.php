<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Clean up map table
        Schema::table('map', function (Blueprint $table) {
            if (Schema::hasColumn('map', 'uuid')) {
                 // In SQLite, dropping a column that is part of a unique index might require explicitly dropping the index first if Laravel doesn't handle it.
                 // But since 'uuid' was made unique, we should drop that index.
                 // The index name is likely 'map_uuid_unique' (standard) or implicitly named.
                 try { $table->dropUnique(['uuid']); } catch (\Throwable $e) {}
                 $table->dropColumn('uuid');
            }
            if (Schema::hasColumn('map', 'id2')) {
                $table->dropColumn('id2');
            }
        });

        // 2. Clean up game_map
        if (Schema::hasTable('game_map')) {
             try {
                Schema::table('game_map', function (Blueprint $table) {
                     $table->dropForeign(['map_uuid']);
                });
             } catch (\Throwable $e) {}

             try {
                Schema::table('game_map', function (Blueprint $table) {
                     $table->dropIndex('game_map_map_uuid_idx');
                });
             } catch (\Throwable $e) {}

             if (Schema::hasColumn('game_map', 'map_uuid')) {
                 Schema::table('game_map', function (Blueprint $table) {
                     $table->dropColumn('map_uuid');
                 });
             }
            
            // Re-add FK if missing
            Schema::table('game_map', function (Blueprint $table) {
                 // Attempt to add FK, if it fails (exists), obscure error
                 try {
                     $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
                 } catch (\Throwable $e) {}
            });
        }
        
        // 3. Clean up cell
        if (Schema::hasTable('cell')) {
             try {
                Schema::table('cell', function (Blueprint $table) {
                     $table->dropForeign(['map_uuid']);
                });
             } catch (\Throwable $e) {}

             try {
                Schema::table('cell', function (Blueprint $table) {
                     $table->dropIndex('cell_map_uuid_idx');
                });
             } catch (\Throwable $e) {}

             if (Schema::hasColumn('cell', 'map_uuid')) {
                 Schema::table('cell', function (Blueprint $table) {
                     $table->dropColumn('map_uuid');
                 });
             }
        }
        
        // 4. Clean up tile
        if (Schema::hasTable('tile')) {
             try {
                Schema::table('tile', function (Blueprint $table) {
                     $table->dropForeign(['map_uuid']);
                });
             } catch (\Throwable $e) {}

             try {
                Schema::table('tile', function (Blueprint $table) {
                     $table->dropIndex('tile_map_uuid_idx');
                });
             } catch (\Throwable $e) {}

             if (Schema::hasColumn('tile', 'map_uuid')) {
                 Schema::table('tile', function (Blueprint $table) {
                     $table->dropColumn('map_uuid');
                 });
             }
        }
    }


    public function down(): void
    {
        // This migration cleans up the schema. Rollback is not supported.
    }
};
