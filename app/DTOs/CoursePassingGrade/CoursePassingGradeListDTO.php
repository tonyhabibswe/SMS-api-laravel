<?php

namespace App\DTOs\CoursePassingGrade;

use App\Models\CoursePassingGrade;

class CoursePassingGradeListDTO
{
    public int $id;
    public int $majorId;
    public string $majorName;
    public int $semesterId;
    public string $semesterName;
    public int $courseId;
    public string $courseName;
    public string $courseCode;
    public float $gradeValue;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        int $id,
        int $majorId,
        string $majorName,
        int $semesterId,
        string $semesterName,
        int $courseId,
        string $courseName,
        string $courseCode,
        float $gradeValue,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->majorId = $majorId;
        $this->majorName = $majorName;
        $this->semesterId = $semesterId;
        $this->semesterName = $semesterName;
        $this->courseId = $courseId;
        $this->courseName = $courseName;
        $this->courseCode = $courseCode;
        $this->gradeValue = $gradeValue;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create DTO from CoursePassingGrade model.
     */
    public static function fromModel(CoursePassingGrade $passingGrade): self
    {
        return new self(
            $passingGrade->id,
            $passingGrade->major_id,
            $passingGrade->major->display_name,
            $passingGrade->semester_id,
            $passingGrade->semester->name,
            $passingGrade->course_id,
            $passingGrade->course->name,
            $passingGrade->course->code,
            (float) $passingGrade->grade_value,
            $passingGrade->created_at->toISOString(),
            $passingGrade->updated_at->toISOString()
        );
    }

    /**
     * Convert DTO to array for API response.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'major_id' => $this->majorId,
            'major_name' => $this->majorName,
            'semester_id' => $this->semesterId,
            'semester_name' => $this->semesterName,
            'course_id' => $this->courseId,
            'course_name' => $this->courseName,
            'course_code' => $this->courseCode,
            'grade_value' => $this->gradeValue,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
