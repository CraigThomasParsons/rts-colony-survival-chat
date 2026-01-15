<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('map', function (Blueprint $table) {
            if (!Schema::hasColumn('map', 'status')) {
                $table->string('status')->default('pending')->after('seed');
            }
            if (!Schema::hasColumn('map', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('map', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('validated_at');
            }
            if (!Schema::hasColumn('map', 'validation_errors')) {
                $table->json('validation_errors')->nullable()->after('started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('map', function (Blueprint $table) {
            if (Schema::hasColumn('map', 'validation_errors')) {
                $table->dropColumn('validation_errors');
            }
            if (Schema::hasColumn('map', 'started_at')) {
                $table->dropColumn('started_at');
            }
            if (Schema::hasColumn('map', 'validated_at')) {
                $table->dropColumn('validated_at');
            }
            if (Schema::hasColumn('map', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
