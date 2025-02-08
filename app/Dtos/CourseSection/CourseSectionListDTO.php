<?php

namespace App\DTOs\CourseSection;

class CourseSectionListDTO
{
    public int $id;
    public string $code;
    public string $name;
    public string $time;
    public ?array $session;

    public function __construct(int $id, string $code, string $name, string $time, ?array $session = null)
    {
        $this->id     = $id;
        $this->code   = $code;
        $this->name   = $name;
        $this->time   = $time;
        $this->session = $session;
    }

    /**
     * Create a DTO from a CourseSection model.
     */
    public static function fromModel($courseSection): self
    {
        // Prepare the session data if the firstSession relation is loaded and exists.
        $sessionData = null;
        if ($courseSection->relationLoaded('firstSession') && $courseSection->firstSession) {
            $sessionData = [
                'id'            => $courseSection->firstSession->id,
                'room'          => $courseSection->firstSession->room,
                'session_start' => $courseSection->firstSession->session_start,
                'session_end'   => $courseSection->firstSession->session_end,
            ];
        }

        return new self(
            $courseSection->id,
            $courseSection->course->code,
            $courseSection->course->name,
            $courseSection->time,
            $sessionData
        );
    }
}
