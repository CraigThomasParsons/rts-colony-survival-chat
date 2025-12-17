<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop dependent tables first (due to foreign key constraints)
        Schema::dropIfExists('tile');
        Schema::dropIfExists('cell');
        Schema::dropIfExists('game_map');
        Schema::dropIfExists('map');
    }

    public function down(): void
    {
        // Note: This down() is intentionally empty as recreating these tables
        // would require all their original migration definitions.
        // If you need to rollback, restore from a database backup.
    }
};
