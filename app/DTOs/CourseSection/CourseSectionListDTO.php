<?php

namespace App\DTOs\CourseSection;

class CourseSectionListDTO
{
    public int $id;
    public string $code;
    public string $name;
    public string $time;
    public string $sectionCode;
    public ?string $room;
    public ?array $session;

    public function __construct(int $id, string $code, string $name, string $time, string $sectionCode, ?string $room, ?array $session = null)
    {
        $this->id           = $id;
        $this->code         = $code;
        $this->name         = $name;
        $this->time         = $time;
        $this->sectionCode = $sectionCode;
        $this->session      = $session;
        $this->room        = $room;
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
                'sessionStart' => $courseSection->firstSession->session_start,
                'sessionEnd'   => $courseSection->firstSession->session_end,
            ];
        }
        return new self(
            $courseSection->id,
            $courseSection->course->code,
            $courseSection->course->name,
            $courseSection->time,
            $courseSection->section_code,
            $sessionData["room"] ?? null,
            $sessionData
        );
    }
}
