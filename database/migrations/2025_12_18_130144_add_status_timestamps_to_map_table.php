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
        Schema::table('map', function (Blueprint $table) {
            // Track when each major status transition occurred for debugging
            $table->timestamp('generation_started_at')->nullable()->after('status');
            $table->timestamp('generation_completed_at')->nullable()->after('generation_started_at');
            $table->timestamp('failed_at')->nullable()->after('generation_completed_at');
            $table->string('last_completed_step')->nullable()->after('failed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map', function (Blueprint $table) {
            $table->dropColumn([
                'generation_started_at',
                'generation_completed_at',
                'failed_at',
                'last_completed_step',
            ]);
        });
    }
};
