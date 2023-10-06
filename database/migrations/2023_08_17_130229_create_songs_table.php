<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // name of the song set by user/name of the file when uploaded
            $table->unsignedBigInteger('duration_sec')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('disk')->nullable();
            //name of the disk if multiplte disks are needed
            $table->string('song_path');
            //path to song on given disk
            $table->string('cover_path')->nullable();
            $table->enum('song_status', ['banned', 'published', 'waiting'])->default('published');

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
            // number of song in given album

            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('songs');
    }
};