<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Remove extra uuid/map_uuid columns that were added during earlier migration attempts
        // The map table already has id as UUID primary key; no need for extra columns
        
        // Clean up map table: drop extra uuid column if present (keep only id which is the PK)
        if (Schema::hasColumn('map', 'uuid') && Schema::hasColumn('map', 'id')) {
            // Ensure id is the actual PK and uuid is redundant
            DB::statement('ALTER TABLE `map` DROP COLUMN `uuid`');
        }
        
        // Clean up map table: drop id2 if present
        if (Schema::hasColumn('map', 'id2')) {
            DB::statement('ALTER TABLE `map` DROP COLUMN `id2`');
        }
        
        // Clean up game_map: remove map_uuid if present (map_id is the FK)
        if (Schema::hasTable('game_map')) {
            // Drop any FK constraints first
            try { DB::statement('ALTER TABLE `game_map` DROP FOREIGN KEY `game_map_map_uuid_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `game_map` DROP INDEX `game_map_map_uuid_foreign`'); } catch (\Throwable $e) {}
            
            if (Schema::hasColumn('game_map', 'map_uuid')) {
                DB::statement('ALTER TABLE `game_map` DROP COLUMN `map_uuid`');
            }
            
            // Ensure FK on map_id if not present
            $fkExists = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                 WHERE TABLE_NAME='game_map' AND COLUMN_NAME='map_id' AND CONSTRAINT_NAME LIKE '%foreign%'"
            );
            
            if (!$fkExists) {
                Schema::table('game_map', function (Blueprint $table) {
                    $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
                });
            }
        }
        
        // Clean up cell: remove map_uuid if present
        if (Schema::hasTable('cell')) {
            try { DB::statement('ALTER TABLE `cell` DROP FOREIGN KEY `cell_map_uuid_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `cell` DROP INDEX `cell_map_uuid_foreign`'); } catch (\Throwable $e) {}
            
            if (Schema::hasColumn('cell', 'map_uuid')) {
                DB::statement('ALTER TABLE `cell` DROP COLUMN `map_uuid`');
            }
            
            // Ensure FK on map_id if not present
            $fkExists = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                 WHERE TABLE_NAME='cell' AND COLUMN_NAME='map_id' AND CONSTRAINT_NAME LIKE '%foreign%'"
            );
            
            if (!$fkExists) {
                Schema::table('cell', function (Blueprint $table) {
                    $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
                });
            }
        }
        
        // Clean up tile: remove map_uuid if present
        if (Schema::hasTable('tile')) {
            try { DB::statement('ALTER TABLE `tile` DROP FOREIGN KEY `tile_map_uuid_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `tile` DROP INDEX `tile_map_uuid_foreign`'); } catch (\Throwable $e) {}
            
            if (Schema::hasColumn('tile', 'map_uuid')) {
                DB::statement('ALTER TABLE `tile` DROP COLUMN `map_uuid`');
            }
            
            // Ensure FK on map_id if not present
            $fkExists = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                 WHERE TABLE_NAME='tile' AND COLUMN_NAME='map_id' AND CONSTRAINT_NAME LIKE '%foreign%'"
            );
            
            if (!$fkExists) {
                Schema::table('tile', function (Blueprint $table) {
                    $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // This migration cleans up the schema. Rollback is not supported.
    }
};
