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
        Schema::table('course_sections', function (Blueprint $table) {
            $table->string('curve_algorithm', 50)
                ->nullable()
                ->after('time')
                ->comment('Curve algorithm to apply: NULL (no curve), AVERAGE_BASED, BELL_CURVE, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropColumn('curve_algorithm');
        });
    }
};
