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
        Schema::create('temporary_songs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('token');
            $table->string('song_path');
            $table->string('extension');
            $table->unsignedInteger('size_kb');

            $table->string('cover_path')->nullable();

            $table->unsignedBigInteger('duration_sec')->nullable();


            // metadata fields
            $table->string('title')->nullable();
            $table->string('artist')->nullable();
            $table->string('album')->nullable();
            $table->date('year')->nullable();
            $table->text('comment')->nullable();
            $table->string('composer')->nullable();
            $table->string('copyright_message')->nullable();
            $table->string('publisher')->nullable();
            $table->string('genre')->nullable();
            $table->text('lyrics')->nullable();
            $table->unsignedSmallInteger('track_number')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_songs');
    }
};
