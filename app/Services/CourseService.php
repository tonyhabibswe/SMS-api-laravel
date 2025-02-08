<?php

namespace App\Services;

use App\DTOs\Course\CourseCreateDTO;
use App\DTOs\Course\CourseEditDTO;
use App\DTOs\Course\CourseListDTO;
use App\Repositories\CourseRepository;
use Illuminate\Support\Collection;

class CourseService
{
    protected CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Get all courses as a collection of CourseListDTO.
     *
     * @return Collection|CourseListDTO[]
     */
    public function listCourses(): Collection
    {
        $courses = $this->courseRepository->getAllCourses();

        return $courses->map(function ($course) {
            return CourseListDTO::fromModel($course);
        });
    }

    /**
     * Create a new course and return its DTO.
     *
     * @param CourseCreateDTO $dto
     * @return CourseListDTO
     */
    public function createCourse(CourseCreateDTO $dto): CourseListDTO
    {
        $course = $this->courseRepository->createCourse($dto);
        
        // Convert the newly created course into a CourseListDTO.
        return CourseListDTO::fromModel($course);
    }

    /**
     * Update a course and return a DTO representing the updated course.
     *
     * @param CourseEditDTO $dto
     * @return CourseListDTO|null
     */
    public function updateCourse(CourseEditDTO $dto): ?CourseListDTO
    {
        $course = $this->courseRepository->updateCourse($dto);
        if (!$course) {
            return null;
        }
        
        // Convert the updated course model into a CourseListDTO.
        return CourseListDTO::fromModel($course);
    }

    /**
     * Delete a course by its ID.
     *
     * @param int $id
     * @return bool True if deletion succeeded, false otherwise.
     */
    public function deleteCourse(int $id): bool
    {
        return $this->courseRepository->deleteCourse($id);
    }
}
