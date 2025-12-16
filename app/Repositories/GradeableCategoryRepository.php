<?php

namespace App\Repositories;

use App\Models\GradeableCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GradeableCategoryRepository
{
    /**
     * Get all gradeable categories with relationships.
     */
    public function getAll(): Collection
    {
        return GradeableCategory::with(['courseSection', 'gradeableItems'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Find a gradeable category by ID with relationships.
     */
    public function findById(int $id): ?GradeableCategory
    {
        return GradeableCategory::with(['courseSection', 'gradeableItems'])->find($id);
    }

    /**
     * Find a gradeable category by ID or throw exception.
     */
    public function findByIdOrFail(int $id): GradeableCategory
    {
        return GradeableCategory::with(['courseSection', 'gradeableItems'])->findOrFail($id);
    }

    /**
     * Get all categories for a specific course section.
     */
    public function getByCourseSection(int $courseSectionId): Collection
    {
        return GradeableCategory::with(['courseSection', 'gradeableItems'])
            ->where('course_section_id', $courseSectionId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new gradeable category.
     */
    public function create(array $data): GradeableCategory
    {
        return GradeableCategory::create($data);
    }

    /**
     * Update a gradeable category.
     */
    public function update(int $id, array $data): GradeableCategory
    {
        $category = $this->findByIdOrFail($id);
        $category->update($data);
        return $category->fresh(['courseSection', 'gradeableItems']);
    }

    /**
     * Delete a gradeable category.
     */
    public function delete(int $id): bool
    {
        $category = $this->findByIdOrFail($id);
        return $category->delete();
    }

    /**
     * Validate if the total weight sum for a course section equals or is less than 100%.
     *
     * @param int $courseSectionId
     * @param int|null $excludeCategoryId Category ID to exclude from calculation (for updates)
     * @return array ['valid' => bool, 'currentSum' => float, 'available' => float]
     */
    public function validateWeightSum(int $courseSectionId, ?int $excludeCategoryId = null): array
    {
        $query = GradeableCategory::where('course_section_id', $courseSectionId);

        if ($excludeCategoryId) {
            $query->where('id', '!=', $excludeCategoryId);
        }

        $currentSum = $query->sum('weight_percent');
        $currentSum = round(floatval($currentSum), 2);
        $available = round(100.00 - $currentSum, 2);

        return [
            'valid' => $currentSum <= 100.00,
            'currentSum' => $currentSum,
            'available' => $available,
        ];
    }

    /**
     * Get categories with items by course section, ordered for table display.
     */
    public function getCategoriesWithItemsByCourseSectionOrderedForTable(int $courseSectionId): Collection
    {
        return GradeableCategory::with(['gradeableItems' => function ($query) {
            $query->orderBy('created_at');
        }])
            ->where('course_section_id', $courseSectionId)
            ->orderBy('created_at')
            ->get();
    }
}
