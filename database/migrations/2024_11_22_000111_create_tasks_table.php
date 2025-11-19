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
        Schema::dropIfExists('todos');
        Schema::dropIfExists('tasks');

        // Drop the old tasks table and create a new one.
        // Add what you need to in order to connect task list items to statuses.
        Schema::create('tasks', function($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->smallInteger('order')->default(0);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('status_id');
            $table->integer('workLeft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the table doesn't already exist.
        if (Schema::hasTable('todos') === false) {
            Schema::create('todos', function (Blueprint $table) {
                $table->id();
                $table->string('task');
                $table->enum('status', ['open','done'])->default('open');
                $table->timestamps();
            });
        }

        Schema::dropIfExists('tasks');
    }
};
