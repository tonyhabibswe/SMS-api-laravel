<?php

namespace App\Services;

use App\Repositories\CourseEnrollmentRepository;
use App\Repositories\StudentCourseStatusRepository;
use App\Models\CourseEnrollment;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseEnrollmentService
{
    protected CourseEnrollmentRepository $repository;
    protected StudentCourseStatusRepository $statusRepository;

    public function __construct(
        CourseEnrollmentRepository $repository,
        StudentCourseStatusRepository $statusRepository
    ) {
        $this->repository = $repository;
        $this->statusRepository = $statusRepository;
    }

    /**
     * Get all enrollments.
     */
    public function getAllEnrollments(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get enrollment by ID.
     */
    public function getEnrollmentById(int $id): ?CourseEnrollment
    {
        return $this->repository->findById($id);
    }

    /**
     * Get enrollments by course section ID.
     */
    public function getEnrollmentsBySectionId(int $sectionId): Collection
    {
        return $this->repository->findBySectionId($sectionId);
    }

    /**
     * Get enrollments by student ID.
     */
    public function getEnrollmentsByStudentId(int $studentId): Collection
    {
        return $this->repository->findByStudentId($studentId);
    }

    /**
     * Find or create enrollment for a student in a course section.
     */
    public function findOrCreateEnrollment(int $sectionId, int $studentId, int $majorId, int $statusId = 1): CourseEnrollment
    {
        $enrollment = $this->repository->findByStudentAndSection($sectionId, $studentId);

        if ($enrollment) {
            return $enrollment;
        }

        return $this->repository->create([
            'course_section_id' => $sectionId,
            'student_id' => $studentId,
            'major_id' => $majorId,
            'status_id' => $statusId,
        ]);
    }

    /**
     * Create a new enrollment.
     */
    public function createEnrollment(int $sectionId, int $studentId, int $majorId, int $statusId = 1): CourseEnrollment
    {
        // Validate status exists
        if (!$this->statusRepository->findById($statusId)) {
            throw new \Exception("Invalid status_id: Status does not exist.");
        }

        // Check if enrollment already exists
        $existing = $this->repository->findByStudentAndSection($sectionId, $studentId);

        if ($existing) {
            throw new \Exception("Student is already enrolled in this course section.");
        }

        return $this->repository->create([
            'course_section_id' => $sectionId,
            'student_id' => $studentId,
            'major_id' => $majorId,
            'status_id' => $statusId,
        ]);
    }

    /**
     * Update an enrollment.
     */
    public function updateEnrollment(int $id, array $data): CourseEnrollment
    {
        $enrollment = $this->repository->findByIdOrFail($id);

        // Validate status_id if provided
        if (isset($data['status_id']) && !$this->statusRepository->findById($data['status_id'])) {
            throw new \Exception("Invalid status_id: Status does not exist.");
        }

        return $this->repository->update($enrollment, $data);
    }

    /**
     * Delete an enrollment.
     */
    public function deleteEnrollment(int $id): bool
    {
        $enrollment = $this->repository->findByIdOrFail($id);

        return $this->repository->delete($enrollment);
    }

    /**
     * Update final grade and letter grade for an enrollment.
     */
    public function updateGrades(int $id, ?float $finalGrade, ?string $letterGrade): CourseEnrollment
    {
        $enrollment = $this->repository->findByIdOrFail($id);

        return $this->repository->update($enrollment, [
            'final_grade' => $finalGrade,
            'letter_grade' => $letterGrade,
        ]);
    }
}
