<?php

namespace App\Repositories;

use App\Models\CourseEnrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseEnrollmentRepository
{
    /**
     * Get all enrollments.
     */
    public function getAll(): Collection
    {
        return CourseEnrollment::with(['student', 'major', 'courseSection', 'status'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Get all enrollments for a course section.
     */
    public function findBySectionId(int $courseSectionId): Collection
    {
        return CourseEnrollment::with(['student', 'major', 'courseSection', 'status'])
            ->where('course_section_id', $courseSectionId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Get enrollments by student.
     */
    public function findByStudentId(int $studentId): Collection
    {
        return CourseEnrollment::with(['courseSection', 'major', 'status'])
            ->where('student_id', $studentId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Find enrollment by ID.
     */
    public function findById(int $id): ?CourseEnrollment
    {
        return CourseEnrollment::with(['student', 'major', 'courseSection', 'status'])->find($id);
    }

    /**
     * Find enrollment by ID or fail.
     */
    public function findByIdOrFail(int $id): CourseEnrollment
    {
        return CourseEnrollment::with(['student', 'major', 'courseSection', 'status'])->findOrFail($id);
    }

    /**
     * Find enrollment by course section and student.
     */
    public function findByStudentAndSection(int $sectionId, int $studentId): ?CourseEnrollment
    {
        return CourseEnrollment::where('course_section_id', $sectionId)
            ->where('student_id', $studentId)
            ->with(['student', 'major', 'courseSection', 'status'])
            ->first();
    }

    /**
     * Create a new enrollment.
     */
    public function create(array $data): CourseEnrollment
    {
        return CourseEnrollment::create($data);
    }

    /**
     * Update an enrollment.
     */
    public function update(CourseEnrollment $enrollment, array $data): CourseEnrollment
    {
        $enrollment->update($data);
        return $enrollment->fresh([
            'student',
            'major',
            'courseSection.course',
            'courseSection.semester',
            'status'
        ]);
    }

    /**
     * Delete an enrollment.
     */
    public function delete(CourseEnrollment $enrollment): bool
    {
        return $enrollment->delete();
    }

    /**
     * Check if enrollment exists.
     */
    public function exists(int $courseSectionId, int $studentId): bool
    {
        return CourseEnrollment::where('course_section_id', $courseSectionId)
            ->where('student_id', $studentId)
            ->exists();
    }
}
