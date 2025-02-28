<?php

namespace App\Services;

use App\DTOs\Session\SessionCreateDTO;
use App\DTOs\Session\SessionListDTO;
use App\Repositories\CourseSessionRepository;
use App\Repositories\AttendanceRepository;
use App\Models\CourseSection;
use App\Repositories\CourseSectionRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class CourseSessionService
{
    protected CourseSectionRepository $courseSectionRepository;
    protected CourseSessionRepository $courseSessionRepository;
    protected AttendanceRepository $attendanceRepository;

    public function __construct(
        CourseSectionRepository $courseSectionRepository,
        CourseSessionRepository $courseSessionRepository,
        AttendanceRepository $attendanceRepository
    ) {
        $this->courseSectionRepository = $courseSectionRepository;
        $this->courseSessionRepository = $courseSessionRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Create a new session for the given course section and attach attendance records.
     *
     * @param SessionCreateDTO $dto
     * @param int $courseSectionId
     * @return \App\Models\CourseSession
     * @throws Exception if course section is not found.
     */
    public function createSessionForCourseSection(SessionCreateDTO $dto, int $courseSectionId)
    {
        // Retrieve the CourseSection model.
        $courseSection = CourseSection::find($courseSectionId);
        if (!$courseSection) {
            throw new Exception("Course section not found", 404);
        }

        $session = null;
        DB::transaction(function () use ($dto, $courseSection, &$session, $courseSectionId) {
            // Create the new session.
            $session = $this->courseSessionRepository->createSession([
                'course_section_id' => $courseSection->id,
                'room'              => $dto->room,
                'session_start'     => $dto->sessionStart,
                'session_end'       => $dto->sessionEnd,
            ]);

            // Retrieve distinct student IDs already enrolled in this course section.
            $studentIds = $this->courseSectionRepository->getDistinctStudentIdsByCourseSection($courseSectionId);
            // For each student, create a new attendance record for the new session.
            foreach ($studentIds as $studentId) {
                $this->attendanceRepository->createSingleAttendanceForStudent($session->id, $studentId);
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
