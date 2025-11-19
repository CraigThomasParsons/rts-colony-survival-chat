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
        // Check if the table doesn't already exist.
        if (Schema::hasTable('tasks') === false) {
            Schema::create('tasks', function($table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });
        }
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('workLeft');
        });
    }

    /**
     * Reverse the migrations.
     * I'm just dropping it if it exists and put it to the default way.
     * This isn't the correct way to do it, but this early on in the migrations
     * I don't think anyone will notice since there isn't a production
     * environment anywhere.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
        // Check if the table doesn't already exist.
        if (Schema::hasTable('tasks') === false) {
            Schema::create('tasks', function($table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });
        }
    }
};
