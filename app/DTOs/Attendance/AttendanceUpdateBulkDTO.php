<?php

namespace App\DTOs\Attendance;

class AttendanceUpdateBulkDTO
{
    /** @var array */
    public array $attendanceIds;
    
    /** @var string */
    public string $attendance;

    public function __construct(array $attendanceIds, string $attendance)
    {
        $this->attendance = $attendance;
        $this->attendanceIds = $attendanceIds;
    }
}
