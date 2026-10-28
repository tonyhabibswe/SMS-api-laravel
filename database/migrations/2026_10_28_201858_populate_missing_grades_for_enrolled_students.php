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
        // A new gradeable item only got grades for the students enrolled at the
        // time, so students added to a section (or set back to enrolled) after
        // its items were created have none for them. Give every enrolled student
        // an empty grade for each item of their section that they have none for.
        $now = now();

        $missingGrades = DB::table('course_enrollments')
            ->join('gradeable_categories', 'gradeable_categories.course_section_id', '=', 'course_enrollments.course_section_id')
            ->join('gradeable_items', 'gradeable_items.category_id', '=', 'gradeable_categories.id')
            ->where('course_enrollments.status_id', 1) // Only active enrollments
            ->whereNotExists(function ($query) {
                $query->from('grades')
                    ->whereColumn('grades.gradeable_item_id', 'gradeable_items.id')
                    ->whereColumn('grades.course_enrollment_id', 'course_enrollments.id');
            })
            ->select('gradeable_items.id', 'course_enrollments.id')
            ->selectRaw('null, ?, ?', [$now, $now]);

        $created = DB::table('grades')->insertOrIgnoreUsing(
            ['gradeable_item_id', 'course_enrollment_id', 'grade_value', 'created_at', 'updated_at'],
            $missingGrades
        );

        Log::info("Created {$created} missing grades for enrolled students");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The created grades are kept: they are empty like any grade not entered
        // yet, and some may have been graded since.
    }
};
