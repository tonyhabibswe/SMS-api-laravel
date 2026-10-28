<?php

namespace App\Services;

use App\DTOs\GradeableCategory\GradeableCategoryCreateDTO;
use App\DTOs\GradeableCategory\GradeableCategoryUpdateDTO;
use App\DTOs\GradeableCategory\GradeableCategoryListDTO;
use App\DTOs\GradeableCategory\GradeableCategoryDetailDTO;
use App\Models\GradeableCategory;
use App\Repositories\GradeableCategoryRepository;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class GradeableCategoryService
{
    protected GradeableCategoryRepository $repository;

    public function __construct(GradeableCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all gradeable categories as DTOs.
     */
    public function getAllCategories(): Collection
    {
        $categories = $this->repository->getAll();
        return $categories->map(fn($category) => GradeableCategoryListDTO::fromModel($category));
    }

    /**
     * Get all categories for a specific course section.
     */
    public function getCategoriesByCourseSection(int $courseSectionId): Collection
    {
        $categories = $this->repository->getByCourseSection($courseSectionId);
        return $categories->map(fn($category) => GradeableCategoryListDTO::fromModel($category));
    }

    /**
     * Get a specific category by ID.
     */
    public function getCategoryById(int $id): GradeableCategoryDetailDTO
    {
        $category = $this->repository->findByIdOrFail($id);
        return GradeableCategoryDetailDTO::fromModel($category);
    }

    /**
     * Create a new gradeable category with weight validation.
     */
    public function createCategory(GradeableCategoryCreateDTO $dto): GradeableCategoryDetailDTO
    {
        // Validate weight sum
        $weightCheck = $this->repository->validateWeightSum($dto->courseSectionId);

        if ($weightCheck['currentSum'] + $dto->weightPercent > 100.00) {
            throw ValidationException::withMessages([
                'weightPercent' => [
                    sprintf(
                        'Adding %.2f%% would exceed 100%%. Current total: %.2f%%. Available: %.2f%%.',
                        $dto->weightPercent,
                        $weightCheck['currentSum'],
                        $weightCheck['available']
                    )
                ]
            ]);
        }

        $category = $this->repository->create([
            'course_section_id' => $dto->courseSectionId,
            'name' => $dto->name,
            'weight_percent' => $dto->weightPercent,
            'algorithm' => $dto->algorithm,
        ]);

        return GradeableCategoryDetailDTO::fromModel($category->load(['courseSection', 'gradeableItems']));
    }

    /**
     * Update a gradeable category with weight validation.
     */
    public function updateCategory(int $id, GradeableCategoryUpdateDTO $dto): GradeableCategoryDetailDTO
    {
        $category = $this->repository->findByIdOrFail($id);

        // Validate weight sum if weight is being updated
        if ($dto->weightPercent !== null) {
            $weightCheck = $this->repository->validateWeightSum(
                $category->course_section_id,
                $id // Exclude current category from sum
            );

            if ($weightCheck['currentSum'] + $dto->weightPercent > 100.00) {
                throw ValidationException::withMessages([
                    'weightPercent' => [
                        sprintf(
                            'Updating to %.2f%% would exceed 100%%. Current total (excluding this): %.2f%%. Available: %.2f%%.',
                            $dto->weightPercent,
                            $weightCheck['currentSum'],
                            $weightCheck['available']
                        )
                    ]
                ]);
            }
        }

        $updateData = array_filter([
            'name' => $dto->name,
            'weight_percent' => $dto->weightPercent,
            'algorithm' => $dto->algorithm,
        ], fn($value) => $value !== null);

        $category = $this->repository->update($id, $updateData);

        return GradeableCategoryDetailDTO::fromModel($category);
    }

    /**
     * Delete a gradeable category (cascade deletes items and grades).
     */
    public function deleteCategory(int $id): void
    {
        $this->repository->delete($id);
    }
}
