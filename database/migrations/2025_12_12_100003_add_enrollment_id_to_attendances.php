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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('course_enrollment_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('course_enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['course_enrollment_id']);
            $table->dropIndex(['course_enrollment_id']);
            $table->dropColumn('course_enrollment_id');
        });
    }
};
