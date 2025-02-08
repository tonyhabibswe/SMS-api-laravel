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
        Schema::table('course_sessions', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
            $table->foreignId('course_section_id')->constrained('course_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_sessions', function (Blueprint $table) {
            $table->dropForeign(['course_section_id']);
            $table->dropColumn('course_section_id');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
        });
    }
};
