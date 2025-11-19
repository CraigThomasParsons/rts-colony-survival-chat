<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('game_id');
            $t->string('name')->nullable();
            $t->string('type')->default('village'); // village, ruin, town
            $t->integer('x');
            $t->integer('y');
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
