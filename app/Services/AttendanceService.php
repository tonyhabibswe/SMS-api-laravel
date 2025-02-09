<?php

namespace App\Services;

use App\DTOs\Attendance\AttendanceUpdateBulkDTO;
use App\DTOs\Attendance\AttendanceUpdateDTO;
use App\DTOs\Attendance\StudentAttendanceListDTO;
use App\Models\CourseSession;
use App\Repositories\AttendanceRepository;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    protected AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Update the attendance record using the provided DTO.
     * @param int $courseSessionId
     * @return Collection 
     */

    public function listStudentsAttendance(int $courseSessionId): Collection
    {
        $students =  $this->attendanceRepository->listStudentAttendanceBySessionId($courseSessionId);
        return $students->map(function ($student) {
            return StudentAttendanceListDTO::fromDatabaseRow($student);
        });
    }

    /**
     * Update the attendance record using the provided DTO.
     *
     * @param AttendanceUpdateDTO $dto
     * @return bool  True if update succeeded, false if record not found.
     */
    public function updateAttendance(AttendanceUpdateDTO $dto): bool
    {
        $attendance = $this->attendanceRepository->findById($dto->id);
        if (!$attendance) {
            return false;
        }
        return $this->attendanceRepository->updateValue($attendance, $dto->attendance);
    }

    /**
     * Update attendance values for a given session.
     *
     * @param int $sessionId
     * @param AttendanceUpdateValuesDTO $dto
     * @return void
     * @throws Exception
     */
    public function updateBulkAttendanceValues(int $sessionId, AttendanceUpdateBulkDTO $dto): void
    {
        // Verify that the session exists.
        $session = CourseSession::find($sessionId);
        if (!$session) {
            throw new Exception("Session not found", 404);
        }

        DB::transaction(function () use ($sessionId, $dto) {
            // Retrieve attendances that need to be updated with the provided value.
            $attendancesToUpdate = $this->attendanceRepository->getAttendancesByIds($dto->attendanceIds);

            // If no attendances are found in the provided IDs, throw an exception.
            if ($attendancesToUpdate->isEmpty()) {
                throw new Exception("Attendances not found", 404);
            }

            // Update these attendances with the provided attendance value.
            $this->attendanceRepository->updateBulkAttendances($attendancesToUpdate, $dto->attendance);

            // Retrieve attendances for the session that are not in the provided IDs and still have null value.
            $attendancesNotUpdated = $this->attendanceRepository->getAttendancesNotUpdated($sessionId, $dto->attendanceIds);

            // For each of these, set the value to "present".
            $this->attendanceRepository->updateBulkAttendances($attendancesNotUpdated, "present");
        });
    }

    /**
     * Update all attendance values for a given session.
     *
     * @param int $sessionId
     * @param AttendanceUpdateValueDTO $dto
     * @throws Exception if the session or attendances are not found.
     * @return void
     */
    public function updateAllAttendanceValues(int $sessionId, AttendanceUpdateDTO $dto): void
    {
        // Verify that the session exists.
        $session = \App\Models\CourseSession::find($sessionId);
        if (!$session) {
            throw new Exception("Session not found", 404);
        }

        DB::transaction(function () use ($sessionId, $dto) {
            // Retrieve all attendance records for the session.
            $attendances = $this->attendanceRepository->getAttendancesBySessionId($sessionId);

            if ($attendances->isEmpty()) {
                throw new Exception("Attendances not found", 404);
            }

            // Update all attendance records with the provided value.
             $updatedCount = $this->attendanceRepository->updateAllAttendancesBySession($sessionId, $dto->attendance);
            if ($updatedCount === 0) {
                throw new Exception("No attendance records were updated", 404);
            }
        });
    }
}
