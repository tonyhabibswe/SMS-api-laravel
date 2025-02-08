<?php

namespace App\Repositories;

use App\Models\Attendance;

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
}
