<?php

namespace App\Services;

use App\DTOs\CoursePassingGrade\CoursePassingGradeCreateDTO;
use App\DTOs\CoursePassingGrade\CoursePassingGradeUpdateDTO;
use App\DTOs\CoursePassingGrade\CoursePassingGradeListDTO;
use App\DTOs\CoursePassingGrade\CoursePassingGradeDetailDTO;
use App\Models\CoursePassingGrade;
use App\Repositories\CoursePassingGradeRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CoursePassingGradeService
{
    protected CoursePassingGradeRepository $repository;

    public function __construct(CoursePassingGradeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all course passing grades as DTOs.
     */
    public function getAllCoursePassingGrades(): Collection
    {
        $passingGrades = $this->repository->getAll();

        return $passingGrades->map(function ($passingGrade) {
            return CoursePassingGradeListDTO::fromModel($passingGrade);
        });
    }

    /**
     * Get course passing grade by ID as DTO.
     */
    public function getCoursePassingGradeById(int $id): ?CoursePassingGradeDetailDTO
    {
        $passingGrade = $this->repository->findById($id);

        return $passingGrade ? CoursePassingGradeDetailDTO::fromModel($passingGrade) : null;
    }

    /**
     * Create a new course passing grade.
     */
    public function createCoursePassingGrade(CoursePassingGradeCreateDTO $dto): CoursePassingGradeDetailDTO
    {
        // Check if combination already exists
        if ($this->repository->existsByMajorSemesterCourse($dto->majorId, $dto->semesterId, $dto->courseId)) {
            throw ValidationException::withMessages([
                'combination' => ['A passing grade for this major, semester, and course combination already exists.']
            ]);
        }

        // Validate grade value range
        if ($dto->gradeValue < 0 || $dto->gradeValue > 100) {
            throw ValidationException::withMessages([
                'grade_value' => ['Grade value must be between 0 and 100.']
            ]);
        }

        $passingGrade = $this->repository->create([
            'major_id' => $dto->majorId,
            'semester_id' => $dto->semesterId,
            'course_id' => $dto->courseId,
            'grade_value' => $dto->gradeValue,
        ]);

        // Reload with relationships
        $passingGrade = $this->repository->findByIdOrFail($passingGrade->id);

        return CoursePassingGradeDetailDTO::fromModel($passingGrade);
    }

    /**
     * Update an existing course passing grade.
     */
    public function updateCoursePassingGrade(int $id, CoursePassingGradeUpdateDTO $dto): CoursePassingGradeDetailDTO
    {
        $passingGrade = $this->repository->findByIdOrFail($id);

        // Check if new combination already exists (excluding current record)
        if ($this->repository->existsByMajorSemesterCourse($dto->majorId, $dto->semesterId, $dto->courseId, $id)) {
            throw ValidationException::withMessages([
                'combination' => ['A passing grade for this major, semester, and course combination already exists.']
            ]);
        }

        // Validate grade value range
        if ($dto->gradeValue < 0 || $dto->gradeValue > 100) {
            throw ValidationException::withMessages([
                'grade_value' => ['Grade value must be between 0 and 100.']
            ]);
        }

        $updatedPassingGrade = $this->repository->update($passingGrade, [
            'major_id' => $dto->majorId,
            'semester_id' => $dto->semesterId,
            'course_id' => $dto->courseId,
            'grade_value' => $dto->gradeValue,
        ]);

        return CoursePassingGradeDetailDTO::fromModel($updatedPassingGrade);
    }

    /**
     * Delete a course passing grade.
     */
    public function deleteCoursePassingGrade(int $id): bool
    {
        $passingGrade = $this->repository->findByIdOrFail($id);
        return $this->repository->delete($passingGrade);
    }

    /**
     * Get course passing grades by major.
     */
    public function getCoursePassingGradesByMajor(int $majorId): Collection
    {
        $passingGrades = $this->repository->getByMajor($majorId);

        return $passingGrades->map(function ($passingGrade) {
            return CoursePassingGradeListDTO::fromModel($passingGrade);
        });
    }

    /**
     * Get course passing grades by semester.
     */
    public function getCoursePassingGradesBySemester(int $semesterId): Collection
    {
        $passingGrades = $this->repository->getBySemester($semesterId);

        return $passingGrades->map(function ($passingGrade) {
            return CoursePassingGradeListDTO::fromModel($passingGrade);
        });
    }

    /**
     * Get course passing grades by course.
     */
    public function getCoursePassingGradesByCourse(int $courseId): Collection
    {
        $passingGrades = $this->repository->getByCourse($courseId);

        return $passingGrades->map(function ($passingGrade) {
            return CoursePassingGradeListDTO::fromModel($passingGrade);
        });
    }

    /**
     * Find course passing grade by combination.
     */
    public function findByMajorSemesterCourse(int $majorId, int $semesterId, int $courseId): ?CoursePassingGradeDetailDTO
    {
        $passingGrade = $this->repository->findByMajorSemesterCourse($majorId, $semesterId, $courseId);

        return $passingGrade ? CoursePassingGradeDetailDTO::fromModel($passingGrade) : null;
    }
}
