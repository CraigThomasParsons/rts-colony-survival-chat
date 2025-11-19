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
        Schema::dropIfExists('map_statuses');

        Schema::create('map_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description');
            $table->timestamps();
        });

        Schema::table('map', function (Blueprint $table) {
            $table->unsignedBigInteger('mapstatuses_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { 
        Schema::dropIfExists('map_statuses');
    }
};