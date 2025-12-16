<?php

namespace App\Repositories;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class GradeRepository
{
    /**
     * Get all grades with relationships.
     */
    public function getAll(): Collection
    {
        return Grade::with(['gradeableItem', 'courseEnrollment'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Find a grade by ID with relationships.
     */
    public function findById(int $id): ?Grade
    {
        return Grade::with(['gradeableItem', 'courseEnrollment'])->find($id);
    }

    /**
     * Find a grade by ID or throw exception.
     */
    public function findByIdOrFail(int $id): Grade
    {
        return Grade::with(['gradeableItem', 'courseEnrollment'])->findOrFail($id);
    }

    /**
     * Get all grades for a specific gradeable item.
     */
    public function getByGradeableItem(int $gradeableItemId): Collection
    {
        return Grade::with(['gradeableItem', 'courseEnrollment.student'])
            ->where('gradeable_item_id', $gradeableItemId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Get all grades for a specific enrollment.
     */
    public function getByEnrollment(int $enrollmentId): Collection
    {
        return Grade::with(['gradeableItem.category', 'courseEnrollment'])
            ->where('course_enrollment_id', $enrollmentId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Get all grades for a specific course section (through enrollment).
     */
    public function getByCourseSection(int $courseSectionId): Collection
    {
        return Grade::with(['gradeableItem', 'courseEnrollment'])
            ->whereHas('courseEnrollment', function ($query) use ($courseSectionId) {
                $query->where('course_section_id', $courseSectionId);
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * Create multiple grade records at once.
     *
     * @param array $gradesData Array of grade data arrays
     * @return bool
     */
    public function bulkCreate(array $gradesData): bool
    {
        return DB::table('grades')->insert($gradesData);
    }

    /**
     * Update a grade.
     */
    public function update(int $id, array $data): Grade
    {
        $grade = $this->findByIdOrFail($id);
        $grade->update($data);
        return $grade->fresh(['gradeableItem', 'courseEnrollment']);
    }

    /**
     * Delete a grade.
     */
    public function delete(int $id): bool
    {
        $grade = $this->findByIdOrFail($id);
        return $grade->delete();
    }

    /**
     * Get all grades for a course section grouped by enrollment.
     */
    public function getAllGradesForCourseSectionGrouped(int $courseSectionId): Collection
    {
        return Grade::whereHas('gradeableItem.category', fn($q) => $q->where('course_section_id', $courseSectionId))
            ->with(['gradeableItem', 'courseEnrollment'])
            ->get()
            ->groupBy('course_enrollment_id');
    }
}
