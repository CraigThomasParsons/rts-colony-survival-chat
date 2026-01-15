<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('map', function (Blueprint $table) {
            // Add seed column with default NULL
            // The seed is optional and can be used for deterministic map generation
            if (!Schema::hasColumn('map', 'seed')) {
                $table->string('seed')->nullable()->after('is_generating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('map', function (Blueprint $table) {
            $table->dropColumn('seed');
        });
    }
};
