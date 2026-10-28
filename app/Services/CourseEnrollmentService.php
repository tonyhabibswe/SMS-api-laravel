<?php

namespace App\Services;

use App\Exceptions\UserFacingException;
use App\Repositories\CourseEnrollmentRepository;
use App\Repositories\GradeRepository;
use App\Repositories\StudentCourseStatusRepository;
use App\Models\CourseEnrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseEnrollmentService
{
    protected CourseEnrollmentRepository $repository;
    protected StudentCourseStatusRepository $statusRepository;
    protected GradeRepository $gradeRepository;

    public function __construct(
        CourseEnrollmentRepository $repository,
        StudentCourseStatusRepository $statusRepository,
        GradeRepository $gradeRepository
    ) {
        $this->repository = $repository;
        $this->statusRepository = $statusRepository;
        $this->gradeRepository = $gradeRepository;
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
     * Find or create enrollment for a student in a course section, with the
     * grades an active enrollment is missing.
     */
    public function findOrCreateEnrollment(int $sectionId, int $studentId, int $majorId, int $statusId = 1): CourseEnrollment
    {
        return DB::transaction(function () use ($sectionId, $studentId, $majorId, $statusId) {
            $enrollment = $this->repository->findByStudentAndSection($sectionId, $studentId);

            if (!$enrollment) {
                $enrollment = $this->repository->create([
                    'course_section_id' => $sectionId,
                    'student_id' => $studentId,
                    'major_id' => $majorId,
                    'status_id' => $statusId,
                ]);
            }

            $this->createMissingGrades($enrollment);

            return $enrollment;
        });
    }

    /**
     * Create a new enrollment.
     */
    public function createEnrollment(int $sectionId, int $studentId, int $majorId, int $statusId = 1): CourseEnrollment
    {
        // Validate status exists
        if (!$this->statusRepository->findById($statusId)) {
            throw new UserFacingException("Invalid status_id: Status does not exist.");
        }

        // Check if enrollment already exists
        $existing = $this->repository->findByStudentAndSection($sectionId, $studentId);

        if ($existing) {
            throw new UserFacingException("Student is already enrolled in this course section.");
        }

        return DB::transaction(function () use ($sectionId, $studentId, $majorId, $statusId) {
            $enrollment = $this->repository->create([
                'course_section_id' => $sectionId,
                'student_id' => $studentId,
                'major_id' => $majorId,
                'status_id' => $statusId,
            ]);

            $this->createMissingGrades($enrollment);

            return $enrollment;
        });
    }

    /**
     * Update an enrollment. A student set back to enrolled gets the grades
     * they are missing.
     */
    public function updateEnrollment(int $id, array $data): CourseEnrollment
    {
        $enrollment = $this->repository->findByIdOrFail($id);

        // Validate status_id if provided
        if (isset($data['status_id']) && !$this->statusRepository->findById($data['status_id'])) {
            throw new UserFacingException("Invalid status_id: Status does not exist.");
        }

        return DB::transaction(function () use ($enrollment, $data) {
            $enrollment = $this->repository->update($enrollment, $data);

            $this->createMissingGrades($enrollment);

            return $enrollment;
        });
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

    /**
     * Give an active enrollment an empty grade for each gradeable item of its
     * course section that it has none for. A new item only gets grades for the
     * students enrolled at the time, so a student added or set back to enrolled
     * later would otherwise have none for the existing items.
     */
    protected function createMissingGrades(CourseEnrollment $enrollment): void
    {
        if ($enrollment->status_id === 1) { // Only active enrollments
            $this->gradeRepository->createMissingForEnrollment($enrollment->id);
        }
    }
}
