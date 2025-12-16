<?php

namespace App\Services;

use App\DTOs\GradeableItem\GradeableItemCreateDTO;
use App\DTOs\GradeableItem\GradeableItemUpdateDTO;
use App\DTOs\GradeableItem\GradeableItemListDTO;
use App\DTOs\GradeableItem\GradeableItemDetailDTO;
use App\Models\CourseEnrollment;
use App\Models\GradeableItem;
use App\Repositories\GradeableItemRepository;
use App\Repositories\GradeRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradeableItemService
{
    protected GradeableItemRepository $repository;
    protected GradeRepository $gradeRepository;

    public function __construct(
        GradeableItemRepository $repository,
        GradeRepository $gradeRepository
    ) {
        $this->repository = $repository;
        $this->gradeRepository = $gradeRepository;
    }

    /**
     * Get all gradeable items as DTOs.
     */
    public function getAllItems(): Collection
    {
        $items = $this->repository->getAll();
        return $items->map(fn($item) => GradeableItemListDTO::fromModel($item));
    }

    /**
     * Get all items for a specific category.
     */
    public function getItemsByCategory(int $categoryId): Collection
    {
        $items = $this->repository->getByCategory($categoryId);
        return $items->map(fn($item) => GradeableItemListDTO::fromModel($item));
    }

    /**
     * Get all items for a specific course section.
     */
    public function getItemsByCourseSection(int $courseSectionId): Collection
    {
        $items = $this->repository->getByCourseSection($courseSectionId);
        return $items->map(fn($item) => GradeableItemListDTO::fromModel($item));
    }

    /**
     * Get a specific item by ID.
     */
    public function getItemById(int $id): GradeableItemDetailDTO
    {
        $item = $this->repository->findByIdOrFail($id);
        return GradeableItemDetailDTO::fromModel($item);
    }

    /**
     * Create a new gradeable item and automatically create grade records for all enrolled students.
     */
    public function createItem(GradeableItemCreateDTO $dto): GradeableItemDetailDTO
    {
        return DB::transaction(function () use ($dto) {
            // Create the gradeable item
            $item = $this->repository->create([
                'category_id' => $dto->categoryId,
                'title' => $dto->title,
                'max_points' => $dto->maxPoints ?? 100.00,
            ]);

            // Load the category relationship to get course_section_id
            $item->load('category');

            // Get all enrolled students for this course section
            $enrollments = CourseEnrollment::where('course_section_id', $item->category->course_section_id)
                ->where('status_id', 1) // Only active enrollments
                ->get();

            // Prepare grade records for bulk insert
            if ($enrollments->isNotEmpty()) {
                $now = now();
                $gradesData = $enrollments->map(function ($enrollment) use ($item, $now) {
                    return [
                        'gradeable_item_id' => $item->id,
                        'course_enrollment_id' => $enrollment->id,
                        'grade_value' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->toArray();

                // Bulk insert grades
                $this->gradeRepository->bulkCreate($gradesData);
            }

            // Reload with relationships
            $item = $this->repository->findByIdOrFail($item->id);

            return GradeableItemDetailDTO::fromModel($item);
        });
    }

    /**
     * Update a gradeable item.
     */
    public function updateItem(int $id, GradeableItemUpdateDTO $dto): GradeableItemDetailDTO
    {
        $updateData = array_filter([
            'title' => $dto->title,
            'max_points' => $dto->maxPoints,
        ], fn($value) => $value !== null);

        $item = $this->repository->update($id, $updateData);

        return GradeableItemDetailDTO::fromModel($item);
    }

    /**
     * Delete a gradeable item (cascade deletes all grades).
     */
    public function deleteItem(int $id): void
    {
        $this->repository->delete($id);
    }
}
