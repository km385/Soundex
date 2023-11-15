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
            // no originalName
            $table->string('token');
            $table->string('song_path'); // filePath earlier
            $table->string('extension');

            $table->string('cover_path')->nullable();
            $table->enum('song_status', ['banned', 'published', 'waiting'])->default('published');

            $table->unsignedBigInteger('duration_sec')->nullable();


            // metadata fields
            $table->string('title')->nullable(); //song name set in tags
            $table->string('artist')->nullable();
            $table->string('album')->nullable();
            $table->date('year')->nullable(); //song release date
            $table->text('comment')->nullable();
            $table->string('composer')->nullable();
            $table->string('copyright_message')->nullable();
            $table->string('publisher')->nullable();
            $table->string('genre')->nullable();
            $table->text('lyrics')->nullable();
            $table->unsignedSmallInteger('track_number')->nullable();

            $table->timestamps();
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
