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
        Schema::create('course_passing_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('major_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->decimal('grade_value', 5, 2); // Supports grades like 85.50
            $table->timestamps();

            // Unique constraint on the combination to prevent duplicates
            $table->unique(['major_id', 'semester_id', 'course_id'], 'course_passing_grades_unique');

            // Indexes for query optimization
            $table->index(['major_id', 'semester_id']);
            $table->index(['course_id']);
            $table->index(['grade_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_passing_grades');
    }
};
