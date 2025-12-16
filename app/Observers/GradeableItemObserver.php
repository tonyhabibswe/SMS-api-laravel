<?php

namespace App\Observers;

use App\Models\CourseEnrollment;
use App\Models\GradeableItem;
use Illuminate\Support\Facades\DB;

class GradeableItemObserver
{
    /**
     * Handle the GradeableItem "created" event.
     * Automatically create grade records for all enrolled students.
     *
     * Note: This observer is registered for reference, but the actual
     * grade creation logic is implemented in GradeableItemService::createItem()
     * using a database transaction for better control and atomicity.
     */
    public function created(GradeableItem $gradeableItem): void
    {
        // Load the category relationship to get course_section_id
        $gradeableItem->load('category');

        if (!$gradeableItem->category) {
            return;
        }

        // Get all enrolled students for this course section
        $enrollments = CourseEnrollment::where('course_section_id', $gradeableItem->category->course_section_id)
            ->where('status_id', 1) // Only active enrollments
            ->get();

        if ($enrollments->isEmpty()) {
            return;
        }

        // Prepare grade records for bulk insert
        $now = now();
        $gradesData = $enrollments->map(function ($enrollment) use ($gradeableItem, $now) {
            return [
                'gradeable_item_id' => $gradeableItem->id,
                'course_enrollment_id' => $enrollment->id,
                'grade_value' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // Bulk insert grades
        DB::table('grades')->insert($gradesData);
    }

    /**
     * Handle the GradeableItem "updated" event.
     */
    public function updated(GradeableItem $gradeableItem): void
    {
        // No action needed on update
    }

    /**
     * Handle the GradeableItem "deleted" event.
     */
    public function deleted(GradeableItem $gradeableItem): void
    {
        // Cascade deletion is handled by database foreign key constraints
    }
}
