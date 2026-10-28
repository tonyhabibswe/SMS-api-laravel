<?php

namespace App\Repositories;

use App\Models\Grade;
use App\Models\GradeableItem;
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
     * Find a grade by enrollment ID and gradeable item ID.
     */
    public function findByEnrollmentAndItem(int $enrollmentId, int $gradeableItemId): ?Grade
    {
        return Grade::with(['gradeableItem', 'courseEnrollment'])
            ->where('course_enrollment_id', $enrollmentId)
            ->where('gradeable_item_id', $gradeableItemId)
            ->first();
    }

    /**
     * Find a grade by enrollment ID and gradeable item ID or throw exception.
     */
    public function findByEnrollmentAndItemOrFail(int $enrollmentId, int $gradeableItemId): Grade
    {
        $grade = $this->findByEnrollmentAndItem($enrollmentId, $gradeableItemId);

        if (!$grade) {
            throw new ModelNotFoundException(
                "Grade not found for enrollment ID {$enrollmentId} and gradeable item ID {$gradeableItemId}"
            );
        }

        return $grade;
    }

    /**
     * Find a grade by enrollment ID and gradeable item ID, creating an empty one
     * if there is none and the item is in the enrollment's course section.
     * Returns null if the item isn't in the enrollment's course section.
     */
    public function findOrCreateByEnrollmentAndItem(int $enrollmentId, int $gradeableItemId): ?Grade
    {
        $grade = $this->findByEnrollmentAndItem($enrollmentId, $gradeableItemId);

        if ($grade) {
            return $grade;
        }

        $this->createMissingForEnrollment($enrollmentId, $gradeableItemId);

        return $this->findByEnrollmentAndItem($enrollmentId, $gradeableItemId);
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
     * Create an empty grade for each gradeable item of the enrollment's course
     * section that the enrollment has no grade for, like the ones a new item
     * gets for every enrolled student. With a gradeable item ID, only for that
     * item, and only if it is in the enrollment's course section.
     *
     * @return int The number of grades created
     */
    public function createMissingForEnrollment(int $enrollmentId, ?int $gradeableItemId = null): int
    {
        $itemIds = GradeableItem::whereHas('category.courseSection.courseEnrollments', fn($q) => $q->whereKey($enrollmentId))
            ->whereDoesntHave('grades', fn($q) => $q->where('course_enrollment_id', $enrollmentId))
            ->when($gradeableItemId !== null, fn($q) => $q->whereKey($gradeableItemId))
            ->pluck('id');

        $now = now();
        $gradesData = $itemIds->map(function ($itemId) use ($enrollmentId, $now) {
            return [
                'gradeable_item_id' => $itemId,
                'course_enrollment_id' => $enrollmentId,
                'grade_value' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // Skips a grade another request created in the meantime (unique on item + enrollment)
        return DB::table('grades')->insertOrIgnore($gradesData);
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
     * Get all grades for a course section grouped by enrollment, in grade id order.
     */
    public function getAllGradesForCourseSectionGrouped(int $courseSectionId): Collection
    {
        return Grade::whereHas('gradeableItem.category', fn($q) => $q->where('course_section_id', $courseSectionId))
            ->orderBy('id')
            ->get()
            ->groupBy('course_enrollment_id');
    }
}
