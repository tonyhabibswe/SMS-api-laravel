<?php

namespace App\Repositories;

use App\Models\CourseSession;
use Illuminate\Support\Collection;

class CourseSessionRepository
{
    /**
     * Retrieve all sessions for a given course section ID.
     *
     * @param int $courseSectionId
     * @return \Illuminate\Support\Collection
     */
    public function getSessionsByCourseSectionId(int $courseSectionId)
    {
        return CourseSession::with('courseSection')
            ->where('course_section_id', $courseSectionId)
            ->get();
    }

    /**
     * Create a new course session record.
     *
     * @param array $attributes
     * @return CourseSession
     */
    public function createSession(array $attributes)
    {
        return CourseSession::create($attributes);
    }

    /**
     * Retrieve all sessions for a given course section ID with a session_start date <= today.
     *
     * @param int $courseSectionId
     * @return Collection
     */
    public function getSessionsUntilTodayByCourseSectionId(int $courseSectionId): Collection
    {
        return CourseSession::whereDate('session_start', '<=', now()->toDateString())
            ->where('course_section_id', $courseSectionId)
            ->orderByDesc('session_start')
            ->get();
    }

    /**
     * Delete all course sessions for a given semester that match a specific date.
     *
     * @param int $semesterId
     * @param string $holidayDate Date in Y-m-d format.
     * @return int Number of records deleted.
     */
    public function deleteSessionsBySemesterAndDate(int $semesterId, string $holidayDate): int
    {
        return CourseSession::whereHas('courseSection', function($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            })
            ->whereDate('session_start', $holidayDate)
            ->delete();
    }

    
}
