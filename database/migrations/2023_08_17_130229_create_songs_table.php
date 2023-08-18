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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name');
            $table->integer('durationSec')->nullable();
            $table->string('songPath');
            $table->enum('statusSong', ['deleted', 'uploaded'])->nullable();
            $table->string('coverPath')->nullable();
            $table->string('album')->nullable();
            $table->string('artist')->nullable();
            $table->string('disk')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
