<?php

namespace App\Services;

use App\DTOs\Student\StudentAttendanceSummaryDTO;
use App\DTOs\Student\StudentCreateDTO;
use App\Repositories\StudentRepository;
use App\Repositories\CourseSessionRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\CourseSectionRepository;
use App\Services\StudentMajorHistoryService;
use App\Services\CourseEnrollmentService;
use Exception;
use Illuminate\Support\Collection;

class StudentAttendanceService
{
    protected StudentRepository $studentRepository;
    protected CourseSessionRepository $courseSessionRepository;
    protected AttendanceRepository $attendanceRepository;
    protected CourseSectionRepository $courseSectionRepository;
    protected StudentMajorHistoryService $majorHistoryService;
    protected CourseEnrollmentService $enrollmentService;

    public function __construct(
        StudentRepository $studentRepository,
        CourseSessionRepository $courseSessionRepository,
        AttendanceRepository $attendanceRepository,
        CourseSectionRepository $courseSectionRepository,
        StudentMajorHistoryService $majorHistoryService,
        CourseEnrollmentService $enrollmentService
    ) {
        $this->studentRepository         = $studentRepository;
        $this->courseSessionRepository   = $courseSessionRepository;
        $this->attendanceRepository      = $attendanceRepository;
        $this->courseSectionRepository   = $courseSectionRepository;
        $this->majorHistoryService       = $majorHistoryService;
        $this->enrollmentService         = $enrollmentService;
    }

    /**
     * Create a student (if not already existing) and add attendances for all sessions of the course section.
     *
     * @param StudentCreateDTO $dto
     * @param int $courseSectionId
     * @return \App\Models\Student
     * @throws Exception if no sessions are found.
     */
    public function createStudentWithAttendances(StudentCreateDTO $dto, int $courseSectionId)
    {
        // Find or create the student.
        $student = $this->studentRepository->findOrCreateStudent($dto);

        // Get course section to find semester
        $courseSection = \App\Models\CourseSection::find($courseSectionId);
        if (!$courseSection) {
            throw new Exception("Course section not found", 404);
        }

        // Track major change/history
        $this->majorHistoryService->trackMajorChange(
            $student->id,
            $dto->major,
            $courseSection->semester_id
        );

        // Find or create enrollment for this student
        $enrollment = $this->enrollmentService->findOrCreateEnrollment(
            $courseSectionId,
            $student->id,
            $student->major_id,
            1 // status_id = 1 (active)
        );

        // Retrieve sessions for the given course section.
        $sessions = $this->courseSessionRepository->getSessionsByCourseSectionId($courseSectionId);

        if ($sessions->isEmpty()) {
            throw new Exception("This course section doesn't have any session created", 400);
        }

        // Check if an attendance record already exists for the first session for this enrollment.
        $firstSession = $sessions->first();
        $attendanceInDb = $this->attendanceRepository->findAttendance($firstSession->id, $enrollment->id);

        if (!$attendanceInDb) {
            // Create an attendance record for each session.
            $this->attendanceRepository->createAttendancesForEnrollment($sessions, $enrollment->id);
        }

        return $student;
    }

    /**
     * Get student attendance summary DTOs for a given course section.
     *
     * @param int $courseSectionId
     * @return Collection|StudentAttendanceSummaryDTO[]
     */
    public function getStudentAttendanceSummary(int $courseSectionId): Collection
    {
        $rows = $this->studentRepository->getStudentAttendanceSummaryByCourseSectionId($courseSectionId);
        return $rows->map(function ($row) {
            return StudentAttendanceSummaryDTO::fromDatabaseRow($row);
        });
    }
}
