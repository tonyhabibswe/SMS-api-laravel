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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_session_id') // Foreign key to Session
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('student_id') // Foreign key to Student
                ->constrained()
                ->onDelete('cascade');
            $table->string('value', 10)->nullable(); // Optional varchar(10)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
