<?php

namespace App\Services;

use App\DTOs\Attendance\AttendanceUpdateDTO;
use App\Repositories\AttendanceRepository;

class AttendanceService
{
    protected AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
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
}
