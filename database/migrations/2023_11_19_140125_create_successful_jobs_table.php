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
        Schema::create('successful_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name');
            $table->unsignedInteger('time');
            $table->unsignedInteger('user_id')->nullable();
            $table->boolean('is_guest');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('successful_jobs');
    }
};
