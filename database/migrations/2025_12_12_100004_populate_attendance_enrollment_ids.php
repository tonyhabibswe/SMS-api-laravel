<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate course_enrollment_id in attendances based on student_id and course_section
        $updated = DB::statement('
            UPDATE attendances
            SET course_enrollment_id = (
                SELECT ce.id
                FROM course_enrollments ce
                JOIN course_sessions cs ON cs.course_section_id = ce.course_section_id
                WHERE ce.student_id = attendances.student_id
                AND cs.id = attendances.course_session_id
                LIMIT 1
            )
            WHERE course_enrollment_id IS NULL
        ');

        // Check for any attendances that couldn't be mapped
        $unmapped = DB::table('attendances')
            ->whereNull('course_enrollment_id')
            ->count();

        if ($unmapped > 0) {
            Log::warning("Found {$unmapped} attendance records that couldn't be mapped to enrollments");
        }

        Log::info('Populated course_enrollment_id in attendances table');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('attendances')->update(['course_enrollment_id' => null]);
    }
};
