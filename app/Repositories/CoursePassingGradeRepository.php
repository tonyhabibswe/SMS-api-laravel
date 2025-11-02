<?php

namespace App\Repositories;

use App\Models\CoursePassingGrade;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CoursePassingGradeRepository
{
    /**
     * Get all course passing grades with relationships.
     */
    public function getAll(): Collection
    {
        return CoursePassingGrade::with(['major', 'semester', 'course'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Find a course passing grade by ID with relationships.
     */
    public function findById(int $id): ?CoursePassingGrade
    {
        return CoursePassingGrade::with(['major', 'semester', 'course'])->find($id);
    }

    /**
     * Find a course passing grade by ID or throw exception.
     */
    public function findByIdOrFail(int $id): CoursePassingGrade
    {
        return CoursePassingGrade::with(['major', 'semester', 'course'])->findOrFail($id);
    }

    /**
     * Create a new course passing grade.
     */
    public function create(array $data): CoursePassingGrade
    {
        return CoursePassingGrade::create($data);
    }

    /**
     * Update an existing course passing grade.
     */
    public function update(CoursePassingGrade $coursePassingGrade, array $data): CoursePassingGrade
    {
        $coursePassingGrade->update($data);
        return $coursePassingGrade->fresh(['major', 'semester', 'course']);
    }

    /**
     * Delete a course passing grade.
     */
    public function delete(CoursePassingGrade $coursePassingGrade): bool
    {
        return $coursePassingGrade->delete();
    }

    /**
     * Find course passing grade by major, semester, and course combination.
     */
    public function findByMajorSemesterCourse(int $majorId, int $semesterId, int $courseId): ?CoursePassingGrade
    {
        return CoursePassingGrade::where('major_id', $majorId)
            ->where('semester_id', $semesterId)
            ->where('course_id', $courseId)
            ->with(['major', 'semester', 'course'])
            ->first();
    }

    /**
     * Get course passing grades by major.
     */
    public function getByMajor(int $majorId): Collection
    {
        return CoursePassingGrade::where('major_id', $majorId)
            ->with(['major', 'semester', 'course'])
            ->orderBy('semester_id')
            ->orderBy('course_id')
            ->get();
    }

    /**
     * Get course passing grades by semester.
     */
    public function getBySemester(int $semesterId): Collection
    {
        return CoursePassingGrade::where('semester_id', $semesterId)
            ->with(['major', 'semester', 'course'])
            ->orderBy('major_id')
            ->orderBy('course_id')
            ->get();
    }

    /**
     * Get course passing grades by course.
     */
    public function getByCourse(int $courseId): Collection
    {
        return CoursePassingGrade::where('course_id', $courseId)
            ->with(['major', 'semester', 'course'])
            ->orderBy('major_id')
            ->orderBy('semester_id')
            ->get();
    }

    /**
     * Check if a course passing grade exists for the given combination.
     */
    public function existsByMajorSemesterCourse(int $majorId, int $semesterId, int $courseId, ?int $excludeId = null): bool
    {
        $query = CoursePassingGrade::where('major_id', $majorId)
            ->where('semester_id', $semesterId)
            ->where('course_id', $courseId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
