<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Check if map.id is already a UUID/CHAR type
        // If so, this migration has already been partially applied; skip the PK swap
        $idColumn = DB::selectOne(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_NAME='map' AND COLUMN_NAME='id' AND TABLE_SCHEMA=DATABASE()"
        );

        // If id is already CHAR(36), the migration was already partially applied; just cleanup and ensure FKs
        if ($idColumn && strpos($idColumn->COLUMN_TYPE, 'char') !== false) {
            // Map table already has UUID as PK, just ensure child FKs are present
            
            // Clean up any extra columns from earlier attempts
            if (Schema::hasColumn('map', 'uuid')) {
                try { DB::statement('ALTER TABLE `map` DROP COLUMN `uuid`'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('map', 'id2')) {
                try { DB::statement('ALTER TABLE `map` DROP COLUMN `id2`'); } catch (\Throwable $e) {}
            }
            
            // Ensure child tables have proper map_id and FK constraints
            if (Schema::hasTable('game_map')) {
                // Drop old FK if exists
                try { DB::statement('ALTER TABLE `game_map` DROP FOREIGN KEY `game_map_map_id_foreign`'); } catch (\Throwable $e) {}
                try { DB::statement('ALTER TABLE `game_map` DROP FOREIGN KEY `game_map_map_uuid_foreign`'); } catch (\Throwable $e) {}
                
                // Remove map_uuid if exists
                if (Schema::hasColumn('game_map', 'map_uuid')) {
                    try { DB::statement('ALTER TABLE `game_map` DROP COLUMN `map_uuid`'); } catch (\Throwable $e) {}
                }
                
                // Ensure map_id is CHAR(36) and has FK
                if (Schema::hasColumn('game_map', 'map_id')) {
                    try { DB::statement('ALTER TABLE `game_map` MODIFY `map_id` CHAR(36)'); } catch (\Throwable $e) {}
                }
                
                // Re-add FK
                Schema::table('game_map', function (Blueprint $table) {
                    $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
                });
            }
            
            if (Schema::hasTable('cell')) {
                try { DB::statement('ALTER TABLE `cell` DROP FOREIGN KEY `cell_map_id_foreign`'); } catch (\Throwable $e) {}
                try { DB::statement('ALTER TABLE `cell` DROP FOREIGN KEY `cell_map_uuid_foreign`'); } catch (\Throwable $e) {}
                
                if (Schema::hasColumn('cell', 'map_uuid')) {
                    try { DB::statement('ALTER TABLE `cell` DROP COLUMN `map_uuid`'); } catch (\Throwable $e) {}
                }
                
                if (Schema::hasColumn('cell', 'map_id')) {
                    try { DB::statement('ALTER TABLE `cell` MODIFY `map_id` CHAR(36)'); } catch (\Throwable $e) {}
                }
                
                Schema::table('cell', function (Blueprint $table) {
                    $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
                });
            }
            
            if (Schema::hasTable('tile')) {
                try { DB::statement('ALTER TABLE `tile` DROP FOREIGN KEY `tile_map_id_foreign`'); } catch (\Throwable $e) {}
                try { DB::statement('ALTER TABLE `tile` DROP FOREIGN KEY `tile_map_uuid_foreign`'); } catch (\Throwable $e) {}
                
                if (Schema::hasColumn('tile', 'map_uuid')) {
                    try { DB::statement('ALTER TABLE `tile` DROP COLUMN `map_uuid`'); } catch (\Throwable $e) {}
                }
                
                if (Schema::hasColumn('tile', 'map_id')) {
                    try { DB::statement('ALTER TABLE `tile` MODIFY `map_id` CHAR(36)'); } catch (\Throwable $e) {}
                }
                
                Schema::table('tile', function (Blueprint $table) {
                    $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
                });
            }
            
            return; // Already done, skip the rest
        }

        // Original migration logic for fresh DB
        // Ensure map.uuid is filled (safety)
        if (Schema::hasColumn('map', 'uuid')) {
            DB::statement("UPDATE `map` SET `uuid` = COALESCE(`uuid`, UUID())");
        }

        // 1) Prepare children: drop FK constraints to map.id and convert map_id to CHAR(36) with UUID values
        // game_map
        if (Schema::hasTable('game_map')) {
            try { DB::statement('ALTER TABLE `game_map` DROP FOREIGN KEY `game_map_map_id_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `game_map` MODIFY `map_id` CHAR(36)'); } catch (\Throwable $e) {}
            DB::statement('UPDATE `game_map` gm JOIN `map` m ON gm.map_id = m.id SET gm.map_id = m.uuid');
        }
        // cell
        if (Schema::hasTable('cell')) {
            try { DB::statement('ALTER TABLE `cell` DROP FOREIGN KEY `cell_map_id_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `cell` MODIFY `map_id` CHAR(36)'); } catch (\Throwable $e) {}
            DB::statement('UPDATE `cell` c JOIN `map` m ON c.map_id = m.id SET c.map_id = m.uuid');
        }
        // tile
        if (Schema::hasTable('tile')) {
            try { DB::statement('ALTER TABLE `tile` DROP FOREIGN KEY `tile_map_id_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `tile` MODIFY `map_id` CHAR(36)'); } catch (\Throwable $e) {}
            DB::statement('UPDATE `tile` t JOIN `map` m ON t.map_id = m.id SET t.map_id = m.uuid');
        }

        // 2) Switch map primary key to UUID by renaming columns
        Schema::table('map', function (Blueprint $table) {
            // Add new uuid-based id2 to swap into place safely
            if (! Schema::hasColumn('map', 'id2')) {
                $table->uuid('id2')->nullable()->after('uuid');
            }
        });

        DB::statement("UPDATE `map` SET `id2` = `uuid`");

        // Drop AUTO_INCREMENT first (MySQL requires auto column be a key)
        DB::statement('ALTER TABLE `map` MODIFY `id` INT NOT NULL');
        // Now we can drop the primary key
        DB::statement('ALTER TABLE `map` DROP PRIMARY KEY');

        Schema::table('map', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('map', function (Blueprint $table) {
            $table->renameColumn('id2', 'id');
        });

        Schema::table('map', function (Blueprint $table) {
            DB::statement('ALTER TABLE `map` ADD PRIMARY KEY (`id`)');
        });

        // 3) Rewire children: add FK to map.id (now UUID)
        // cell: ensure FK
        if (Schema::hasTable('cell')) {
            // Add FK to map(id)
            Schema::table('cell', function (Blueprint $table) {
                $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('tile')) {
            Schema::table('tile', function (Blueprint $table) {
                $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('game_map')) {
            Schema::table('game_map', function (Blueprint $table) {
                $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // This down() is intentionally partial due to destructive PK swap; would require snapshot restore.
        // We keep the UUID PK and do not revert automatically.
    }
};
