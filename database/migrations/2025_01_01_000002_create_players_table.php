<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('players', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('game_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('name')->nullable();
            $t->json('resources')->nullable();
            $t->boolean('is_bot')->default(false);
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
