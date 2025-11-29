<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_map', function (Blueprint $table) {
            $table->id();
            // games.id is unsigned BIGINT
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id')->references('id')->on('games')->cascadeOnDelete();

            // map.id is INT (not bigIncrements)
            $table->integer('map_id');
            $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();

            $table->timestamps();
            $table->unique(['game_id', 'map_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_map');
    }
};
