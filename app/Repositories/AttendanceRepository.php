<?php

namespace App\Repositories;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceRepository
{
    /**
     * Find an attendance record by its ID.
     *
     * @param int $id
     * @return Attendance|null
     */
    public function findById(int $id): ?Attendance
    {
        return Attendance::find($id);
    }

    /**
     * Update the attendance record's value.
     *
     * @param Attendance $attendance
     * @param string $value
     * @return bool
     */
    public function updateValue(Attendance $attendance, string $value): bool
    {
        $attendance->value = $value;
        return $attendance->save();
    }

    /**
     * Find an attendance record by session ID and student ID.
     *
     * @param int $sessionId
     * @param int $studentId
     * @return Attendance|null
     */
    public function findAttendance(int $sessionId, int $studentId)
    {
        return Attendance::where('course_session_id', $sessionId)
            ->where('student_id', $studentId)
            ->first();
    }

    /**
     * Create an attendance record.
     *
     * @param array $data
     * @return Attendance
     */
    public function createAttendance(array $data)
    {
        return Attendance::create($data);
    }

    /**
     * Create attendance records for a collection of sessions for a given student.
     *
     * @param \Illuminate\Support\Collection $sessions
     * @param int $studentId
     * @return void
     */
    public function createAttendancesForStudent($sessions, int $studentId): void
    {
        foreach ($sessions as $session) {
            $this->createAttendance([
                'course_session_id' => $session->id,
                'student_id'        => $studentId,
                'value'             => null,
            ]);
        }
    }

    /**
     * Create an attendance record for a session and a student.
     *
     * @param int $sessionId
     * @param int $studentId
     * @return Attendance
     */
    public function createSingleAttendanceForStudent(int $sessionId, int $studentId)
    {
        return $this->createAttendance([
            'course_session_id' => $sessionId,
            'student_id'        => $studentId,
            'value'             => null,
        ]);
    }

    /**
     * Get the student attendance summary for a given session.
     *
     * @param int $sessionId
     * @return Collection
     */
    public function listStudentAttendanceBySessionId(int $sessionId): Collection
    {
        return DB::table('attendances')
            ->join('course_sessions', 'attendances.course_session_id', '=', 'course_sessions.id')
            ->join('course_sections', 'course_sessions.course_section_id', '=', 'course_sections.id')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->where('course_sessions.id', '=', $sessionId)
            ->select(
                'attendances.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major',
                'students.email',
                'students.campus',
                'attendances.value as attendance'
            )
            ->groupBy(
                'attendances.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major',
                'students.email',
                'students.campus'
            )
            ->get();
    }


    /**
     * Retrieve attendance records by a list of IDs.
     *
     * @param array $ids
     * @return Collection
     */
    public function getAttendancesByIds(array $ids): Collection
    {
        return Attendance::whereIn('id', $ids)->get();
    }

    /**
     * Retrieve attendance records for a given session ID that are not in the provided list and have a null value.
     *
     * @param int $sessionId
     * @param array $excludeIds
     * @return Collection
     */
    public function getAttendancesNotUpdated(int $sessionId, array $excludeIds): Collection
    {
        return Attendance::where('course_session_id', $sessionId)
            ->whereNotIn('id', $excludeIds)
            ->whereNull('value')
            ->get();
    }

    /**
     * Update a collection of attendances with a given value.
     *
     * @param Collection $attendances
     * @param string $value
     * @return void
     */
    public function updateBulkAttendances(Collection $attendances, string $value): void
    {
        foreach ($attendances as $attendance) {
            $attendance->value = $value;
            $attendance->save();
        }
    }

    /**
     * Retrieve all attendance records for a given session ID.
     *
     * @param int $sessionId
     * @return Collection
     */
    public function getAttendancesBySessionId(int $sessionId): Collection
    {
        return Attendance::where('course_session_id', $sessionId)->get();
    }

    /**
     * Alternatively, update all attendances for a given session using a single query.
     *
     * @param int $sessionId
     * @param string $value
     * @return int Number of records updated.
     */
    public function updateAllAttendancesBySession(int $sessionId, string $value): int
    {
        return Attendance::where('course_session_id', $sessionId)
            ->update(['value' => $value]);
    }


}
