<?php

namespace App\DTOs\Attendance;

class AttendanceUpdateDTO
{
    public int $id;
    public string $attendance;

    public function __construct(int $id, string $attendance)
    {
        $this->id = $id;
        $this->attendance = $attendance;
    }
}
