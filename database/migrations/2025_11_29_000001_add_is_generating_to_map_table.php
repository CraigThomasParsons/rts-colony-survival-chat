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
            if (!Schema::hasColumn('map', 'is_generating')) {
                $table->boolean('is_generating')->default(false)->after('mapstatuses_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map', function (Blueprint $table) {
            if (Schema::hasColumn('map', 'is_generating')) {
                $table->dropColumn('is_generating');
            }
        });
    }
};
