<?php

namespace App\Services;

use App\DTOs\Student\StudentAttendanceSummaryDTO;
use App\DTOs\Student\StudentCreateDTO;
use App\Repositories\StudentRepository;
use App\Repositories\CourseSessionRepository;
use App\Repositories\AttendanceRepository;
use Exception;
use Illuminate\Support\Collection;

class StudentAttendanceService
{
    protected StudentRepository $studentRepository;
    protected CourseSessionRepository $courseSessionRepository;
    protected AttendanceRepository $attendanceRepository;

    public function __construct(
        StudentRepository $studentRepository,
        CourseSessionRepository $courseSessionRepository,
        AttendanceRepository $attendanceRepository
    ) {
        $this->studentRepository         = $studentRepository;
        $this->courseSessionRepository   = $courseSessionRepository;
        $this->attendanceRepository      = $attendanceRepository;
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

        // Retrieve sessions for the given course section.
        $sessions = $this->courseSessionRepository->getSessionsByCourseSectionId($courseSectionId);

        if ($sessions->isEmpty()) {
            throw new Exception("This course section doesn't have any session created", 400);
        }

        // Check if an attendance record already exists for the first session for this student.
        $firstSession = $sessions->first();
        $attendanceInDb = $this->attendanceRepository->findAttendance($firstSession->id, $student->id);

        if (!$attendanceInDb) {
            // Create an attendance record for each session.
            $this->attendanceRepository->createAttendancesForStudent($sessions, $student->id);
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
