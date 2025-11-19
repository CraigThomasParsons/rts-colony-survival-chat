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
        Schema::dropIfExists('buildtypes');
        Schema::create('buildtypes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Going to add a recommended build order.
            $table->smallInteger('order')->default(0);

            // I'm going to try to make sure buildings only have a single requirement.
            $table->unsignedInteger('buildtypeRequirements_id')->nullable();

            $table->string('monetaryValue');
            $table->string('lumber');
            $table->string('buildtime');
            $table->string('hitpoints');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildtypes');
    }
};
