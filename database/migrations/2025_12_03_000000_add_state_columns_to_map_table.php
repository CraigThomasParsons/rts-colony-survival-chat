<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map', function (Blueprint $table) {
            if (!Schema::hasColumn('map', 'state')) {
                $table->string('state')->nullable()->after('description');
            }

            if (!Schema::hasColumn('map', 'next_step')) {
                $table->string('next_step')->nullable()->after('state');
            }
        });
    }

    public function down(): void
    {
        Schema::table('map', function (Blueprint $table) {
            if (Schema::hasColumn('map', 'next_step')) {
                $table->dropColumn('next_step');
            }

            if (Schema::hasColumn('map', 'state')) {
                $table->dropColumn('state');
            }
        });
    }
};
