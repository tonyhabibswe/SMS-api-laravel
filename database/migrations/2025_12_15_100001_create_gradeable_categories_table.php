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
        Schema::create('gradeable_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained('course_sections')->onDelete('cascade');
            $table->string('name', 255);
            $table->decimal('weight_percent', 5, 2); // e.g., 25.50
            $table->enum('algorithm', ['AVERAGE', 'PICK_HIGHEST']);
            $table->timestamps();

            // Index for query performance
            $table->index('course_section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gradeable_categories');
    }
};
