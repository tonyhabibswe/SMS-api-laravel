<?php

namespace App\DTOs\Attendance;

class AttendanceExportDTO
{
    /** @var string */
    public string $fileName;
    
    /** @var string */
    public string $file;

    public function __construct(string $fileName, string $file)
    {
        $this->fileName = $fileName;
        $this->file = $file;
    }
}
