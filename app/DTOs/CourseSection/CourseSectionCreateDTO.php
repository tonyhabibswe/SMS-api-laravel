<?php

namespace App\DTOs\CourseSection;

class CourseSectionCreateDTO
{
    public int $semesterId;
    public int $courseId;
    public string $sectionCode;
    public array $courseDays;
    public string $startSessionTime;
    public string $endSessionTime;
    public string $room;

    public function __construct(
        int $semesterId,
        int $courseId,
        string $sectionCode,
        array $courseDays,
        string $startSessionTime,
        string $endSessionTime,
        string $room
    ) {
        $this->semesterId      = $semesterId;
        $this->courseId        = $courseId;
        $this->sectionCode     = $sectionCode;
        $this->courseDays      = $courseDays;
        $this->startSessionTime = $startSessionTime;
        $this->endSessionTime   = $endSessionTime;
        $this->room             = $room;
    }
}
