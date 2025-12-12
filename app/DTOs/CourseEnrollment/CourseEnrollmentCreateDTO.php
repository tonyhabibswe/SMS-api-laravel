<?php

namespace App\DTOs\CourseEnrollment;

class CourseEnrollmentCreateDTO
{
    public int $courseSectionId;
    public int $studentId;
    public int $majorId;
    public int $statusId;

    public function __construct(
        int $courseSectionId,
        int $studentId,
        int $majorId,
        int $statusId = 1
    ) {
        $this->courseSectionId = $courseSectionId;
        $this->studentId = $studentId;
        $this->majorId = $majorId;
        $this->statusId = $statusId;
    }

    /**
     * Create DTO from request data.
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            (int) $data['course_section_id'],
            (int) $data['student_id'],
            (int) $data['major_id'],
            (int) ($data['status_id'] ?? 1)
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'course_section_id' => $this->courseSectionId,
            'student_id' => $this->studentId,
            'major_id' => $this->majorId,
            'status_id' => $this->statusId,
        ];
    }
}
