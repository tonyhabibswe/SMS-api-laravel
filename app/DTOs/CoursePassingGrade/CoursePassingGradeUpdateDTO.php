<?php

namespace App\DTOs\CoursePassingGrade;

class CoursePassingGradeUpdateDTO
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
            (int) $data['major_id'],
            (int) $data['semester_id'],
            (int) $data['course_id'],
            (float) $data['grade_value']
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
