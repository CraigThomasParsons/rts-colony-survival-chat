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
        Schema::create('cellType', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 32);
            $table->string('description', 128);
            $table->timestamps();
        });

        Schema::create('cell', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 32);
            $table->string('description', 128);
            $table->integer('coordinateX');
            $table->integer('coordinateY');
            $table->integer('height');
            $table->integer('map_id')->index('map_id');
            $table->integer('cellType_id');

            $table->unique(['coordinateX', 'coordinateY', 'map_id'], 'coordinatex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cell');
        Schema::dropIfExists('cellType');
    }
};
