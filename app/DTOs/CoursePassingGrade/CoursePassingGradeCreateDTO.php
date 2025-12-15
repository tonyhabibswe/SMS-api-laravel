<?php

namespace App\DTOs\CoursePassingGrade;

class CoursePassingGradeCreateDTO
{
    public int $majorId;
    public int $semesterId;
    public int $courseId;
    public float $gradeValue;

    public function __construct(
        int $majorId,
        int $semesterId,
        int $courseId,
        float $gradeValue
    ) {
        $this->majorId = $majorId;
        $this->semesterId = $semesterId;
        $this->courseId = $courseId;
        $this->gradeValue = $gradeValue;
    }

    /**
     * Create DTO from request data.
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            (int) $data['majorId'],
            (int) $data['semesterId'],
            (int) $data['courseId'],
            (float) $data['gradeValue']
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'major_id' => $this->majorId,
            'semester_id' => $this->semesterId,
            'course_id' => $this->courseId,
            'grade_value' => $this->gradeValue,
        ];
    }
}
