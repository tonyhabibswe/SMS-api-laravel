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
        // Populate course_enrollments from existing attendance records
        // Group by course_section and student to get unique enrollments
        $enrollments = DB::table('attendances')
            ->join('course_sessions', 'attendances.course_session_id', '=', 'course_sessions.id')
            ->join('course_sections', 'course_sessions.course_section_id', '=', 'course_sections.id')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->select(
                'course_sections.id as course_section_id',
                'students.id as student_id',
                'students.major_id',
                DB::raw('1 as status_id'), // Default to enrolled
                DB::raw('NULL as final_grade'),
                DB::raw('NULL as letter_grade'),
                DB::raw('MIN(attendances.created_at) as created_at'),
                DB::raw('MIN(attendances.updated_at) as updated_at')
            )
            ->groupBy('course_sections.id', 'students.id', 'students.major_id')
            ->get();

        $batchSize = 1000;
        $batch = [];
        $inserted = 0;

        foreach ($enrollments as $enrollment) {
            $batch[] = [
                'course_section_id' => $enrollment->course_section_id,
                'student_id' => $enrollment->student_id,
                'major_id' => $enrollment->major_id,
                'status_id' => $enrollment->status_id,
                'final_grade' => $enrollment->final_grade,
                'letter_grade' => $enrollment->letter_grade,
                'created_at' => $enrollment->created_at,
                'updated_at' => $enrollment->updated_at,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('course_enrollments')->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        // Insert remaining records
        if (!empty($batch)) {
            DB::table('course_enrollments')->insert($batch);
            $inserted += count($batch);
        }

        Log::info("Migrated {$inserted} student enrollments to course_enrollments table");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('course_enrollments')->truncate();
    }
};
