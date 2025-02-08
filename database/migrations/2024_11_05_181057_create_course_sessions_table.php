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
        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id') // Foreign key to Course
                ->constrained()
                ->onDelete('cascade');
            $table->dateTime('session_start');
            $table->dateTime('session_end');
            $table->string('room');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sessions');
    }
};
