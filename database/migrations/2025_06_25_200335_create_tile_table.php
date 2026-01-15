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
            $table->integer('tileType_id')
                ->index('tiletype_id')
                ->default(1);

            $table->index(['cell_id', 'map_id'], 'cell_id');
            $table->foreign('map_id')->references('id')->on('map')->cascadeOnDelete();
            $table->foreign('cell_id')->references('id')->on('cell')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tile');
    }
};
