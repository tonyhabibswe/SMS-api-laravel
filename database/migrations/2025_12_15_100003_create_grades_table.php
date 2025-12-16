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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradeable_item_id')->constrained('gradeable_items')->onDelete('cascade');
            $table->foreignId('course_enrollment_id')->constrained('course_enrollments')->onDelete('cascade');
            $table->decimal('grade_value', 5, 2)->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate grade records
            $table->unique(['gradeable_item_id', 'course_enrollment_id'], 'grades_unique');

            // Indexes for query performance
            $table->index('gradeable_item_id');
            $table->index('course_enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
