<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Recreate the map table with UUID primary key
        Schema::create('map', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 32);
            $table->string('description', 128);
            $table->integer('coordinateX');
            $table->integer('coordinateY');
            $table->unsignedBigInteger('mapstatuses_id')->nullable();
            $table->string('state')->nullable();
            $table->string('next_step')->nullable();
            $table->boolean('is_generating')->default(false);
            $table->string('seed')->nullable();
            $table->timestamps();
        });

        // Recreate the cell table
        Schema::create('cell', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 32);
            $table->string('description', 128);
            $table->integer('coordinateX');
            $table->integer('coordinateY');
            $table->integer('height');
            $table->uuid('map_id');
            $table->integer('cellType_id');
            
            $table->unique(['coordinateX', 'coordinateY', 'map_id'], 'coordinatex');
            $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
        });

        // Recreate the tile table
        Schema::create('tile', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 32);
            $table->string('description', 128);
            $table->integer('coordinateX');
            $table->integer('coordinateY');
            $table->integer('mapCoordinateX');
            $table->integer('mapCoordinateY');
            $table->uuid('cell_id');
            $table->uuid('map_id');
            $table->integer('tileType_id')->default(1);

            $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
            $table->foreign('cell_id')->references('id')->on('cell')->cascadeOnDelete();
        });

        // Recreate game_map pivot table
        Schema::create('game_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->uuid('map_id');
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games')->cascadeOnDelete();
            $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
            $table->unique(['game_id', 'map_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_map');
        Schema::dropIfExists('tile');
        Schema::dropIfExists('cell');
        Schema::dropIfExists('map');
    }
};
