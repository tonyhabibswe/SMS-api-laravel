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
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('major_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('status_id')->default(1); // 1=enrolled, 2=dropped, 3=completed
            $table->integer('final_grade')->nullable(); // 0-100 percentage
            $table->string('letter_grade', 2)->nullable(); // A, B, C, D, F, etc.
            $table->timestamps();

            // Unique constraint to prevent duplicate enrollments
            $table->unique(['course_section_id', 'student_id'], 'course_enrollment_unique');

            // Indexes for query optimization
            $table->index('course_section_id');
            $table->index('student_id');
            $table->index('status_id');
            $table->index('major_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
