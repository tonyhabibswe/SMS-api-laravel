<?php

namespace App\Services;

use App\DTOs\Session\SessionCreateDTO;
use App\DTOs\Session\SessionListDTO;
use App\Repositories\CourseSessionRepository;
use App\Repositories\AttendanceRepository;
use App\Models\CourseSection;
use App\Repositories\CourseEnrollmentRepository;
use Illuminate\Support\Facades\DB;

class CourseSessionService
{
    protected CourseEnrollmentRepository $courseEnrollmentRepository;
    protected CourseSessionRepository $courseSessionRepository;
    protected AttendanceRepository $attendanceRepository;

    public function __construct(
        CourseEnrollmentRepository $courseEnrollmentRepository,
        CourseSessionRepository $courseSessionRepository,
        AttendanceRepository $attendanceRepository
    ) {
        $this->courseEnrollmentRepository = $courseEnrollmentRepository;
        $this->courseSessionRepository = $courseSessionRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Create a new session for the given course section and attach attendance records.
     *
     * @param SessionCreateDTO $dto
     * @param int $courseSectionId
     * @return \App\Models\CourseSession
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if course section is not found.
     */
    public function createSessionForCourseSection(SessionCreateDTO $dto, int $courseSectionId)
    {
        // Retrieve the CourseSection model.
        $courseSection = CourseSection::findOrFail($courseSectionId);

        $session = null;
        DB::transaction(function () use ($dto, $courseSection, &$session) {
            // Create the new session.
            $session = $this->courseSessionRepository->createSession([
                'course_section_id' => $courseSection->id,
                'room'              => $dto->room,
                'session_start'     => $dto->sessionStart,
                'session_end'       => $dto->sessionEnd,
            ]);

            // Create an empty attendance record for the new session for every enrollment
            // in this course section (same as the student import flow: any status, value null).
            $enrollments = $this->courseEnrollmentRepository->findBySectionId($courseSection->id);
            foreach ($enrollments as $enrollment) {
                $this->attendanceRepository->createSingleAttendanceForEnrollment($session->id, $enrollment->id);
            }
        });

        return $session;
    }

    /**
     * Get sessions until today for a given course section as DTOs.
     *
     * @param int $courseSectionId
     * @return \Illuminate\Support\Collection|SessionDTO[]
     */
    public function getSessionsUntilToday(int $courseSectionId)
    {
        $sessions = $this->courseSessionRepository->getSessionsUntilTodayByCourseSectionId($courseSectionId);
        return $sessions->map(function ($session) {
            return SessionListDTO::fromModel($session);
        });
    }
}
