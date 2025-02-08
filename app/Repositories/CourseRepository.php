<?php

namespace App\Repositories;

use App\DTOs\Course\CourseCreateDTO;
use App\DTOs\Course\CourseEditDTO;
use App\Models\Course;
use Illuminate\Support\Collection;

class CourseRepository
{
    /**
     * Retrieve all courses ordered by ascending id.
     *
     * @return Collection
     */
    public function getAllCourses(): Collection
    {
        return Course::orderBy('id')->get();
    }

    /**
     * Create a new course record using the provided DTO.
     *
     * @param CourseCreateDTO $dto
     * @return Course
     */
    public function createCourse(CourseCreateDTO $dto)
    {
        return Course::create($dto->toArray());
    }

    /**
     * Update a course using the provided DTO.
     *
     * @param CourseEditDTO $dto
     * @return Course|null
     */
    public function updateCourse(CourseEditDTO $dto): ?Course
    {
        $course = Course::find($dto->id);
        if (!$course) {
            return null;
        }
        
        // Update fields
        $course->code = $dto->code;
        $course->name = $dto->name;
        $course->save();
        
        return $course;
    }

    /**
     * Delete a course by its ID.
     *
     * @param int $id
     * @return bool True if deletion was successful, false if the course was not found.
     */
    public function deleteCourse(int $id): bool
    {
        $course = Course::find($id);
        if (!$course) {
            return false;
        }
        $course->delete();
        return true;
    }
}
