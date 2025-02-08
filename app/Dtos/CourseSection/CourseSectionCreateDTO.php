<?php

namespace App\DTOs\CourseSection;

class CourseSectionCreateDTO
{
    public int $semester_id;
    public int $course_id;
    public string $section_code;
    public array $course_days;
    public string $start_session_time;
    public string $end_session_time;
    public string $room;

    public function __construct(
        int $semester_id,
        int $course_id,
        string $section_code,
        array $course_days,
        string $start_session_time,
        string $end_session_time,
        string $room
    ) {
        $this->semester_id      = $semester_id;
        $this->course_id        = $course_id;
        $this->section_code     = $section_code;
        $this->course_days      = $course_days;
        $this->start_session_time = $start_session_time;
        $this->end_session_time   = $end_session_time;
        $this->room             = $room;
    }
}
