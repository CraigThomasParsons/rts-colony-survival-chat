<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Add UUID column to map
        Schema::table('map', function (Blueprint $table) {
            if (! Schema::hasColumn('map', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
        });

        // Backfill map.uuid for existing rows
        if (DB::getDriverName() === 'sqlite') {
            DB::table('map')->whereNull('uuid')->cursor()->each(function ($map) {
                DB::table('map')->where('id', $map->id)->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
            });
        } else {
            DB::statement("UPDATE `map` SET `uuid` = UUID() WHERE `uuid` IS NULL");
        }

        // 2) Add map_uuid columns to children tables
        foreach (['cell', 'tile', 'game_map'] as $child) {
            Schema::table($child, function (Blueprint $table) use ($child) {
                if (! Schema::hasColumn($child, 'map_uuid')) {
                    $table->uuid('map_uuid')->nullable()->after('map_id');
                }
            });
        }

        // 3) Backfill child.map_uuid by joining to map.uuid
        // cell
        if (Schema::hasTable('cell')) {
            if (DB::getDriverName() === 'sqlite') {
                 DB::statement("UPDATE `cell` SET `map_uuid` = (SELECT `uuid` FROM `map` WHERE `map`.`id` = `cell`.`map_id`) WHERE `map_uuid` IS NULL");
            } else {
                 DB::statement("UPDATE `cell` c JOIN `map` m ON c.map_id = m.id SET c.map_uuid = m.uuid WHERE c.map_uuid IS NULL");
            }
        }
        // tile
        if (Schema::hasTable('tile')) {
            if (DB::getDriverName() === 'sqlite') {
                 DB::statement("UPDATE `tile` SET `map_uuid` = (SELECT `uuid` FROM `map` WHERE `map`.`id` = `tile`.`map_id`) WHERE `map_uuid` IS NULL");
            } else {
                 DB::statement("UPDATE `tile` t JOIN `map` m ON t.map_id = m.id SET t.map_uuid = m.uuid WHERE t.map_uuid IS NULL");
            }
        }
        // game_map
        if (Schema::hasTable('game_map')) {
            if (DB::getDriverName() === 'sqlite') {
                 DB::statement("UPDATE `game_map` SET `map_uuid` = (SELECT `uuid` FROM `map` WHERE `map`.`id` = `game_map`.`map_id`) WHERE `map_uuid` IS NULL");
            } else {
                 DB::statement("UPDATE `game_map` gm JOIN `map` m ON gm.map_id = m.id SET gm.map_uuid = m.uuid WHERE gm.map_uuid IS NULL");
            }
        }

        // 4) Create FKs on the new map_uuid columns to map.uuid
        // Also add temporary indexes as needed
        Schema::table('cell', function (Blueprint $table) {
            if (Schema::hasColumn('cell', 'map_uuid')) {
                $table->index('map_uuid', 'cell_map_uuid_idx');
                $table->foreign('map_uuid')->references('uuid')->on('map')->cascadeOnDelete();
            }
        });

        Schema::table('tile', function (Blueprint $table) {
            if (Schema::hasColumn('tile', 'map_uuid')) {
                $table->index('map_uuid', 'tile_map_uuid_idx');
                $table->foreign('map_uuid')->references('uuid')->on('map')->cascadeOnDelete();
            }
        });

        Schema::table('game_map', function (Blueprint $table) {
            if (Schema::hasColumn('game_map', 'map_uuid')) {
                $table->index('map_uuid', 'game_map_map_uuid_idx');
                $table->foreign('map_uuid')->references('uuid')->on('map')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Drop FKs and indexes for map_uuid columns then drop the columns
        foreach (['cell', 'tile', 'game_map'] as $child) {
            if (Schema::hasColumn($child, 'map_uuid')) {
                Schema::table($child, function (Blueprint $table) use ($child) {
                    // Attempt to drop FK by column
                    try { $table->dropForeign([$child === 'game_map' ? 'map_uuid' : 'map_uuid']); } catch (\Throwable $e) {}
                    // Drop indexes if exist
                    foreach ([$child.'_map_uuid_idx', 'map_uuid'] as $idx) {
                        try { $table->dropIndex($idx); } catch (\Throwable $e) {}
                    }
                    $table->dropColumn('map_uuid');
                });
            }
        }

        // Drop map.uuid
        if (Schema::hasColumn('map', 'uuid')) {
            Schema::table('map', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
