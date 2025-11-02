<?php

namespace App\DTOs\CoursePassingGrade;

use App\Models\CoursePassingGrade;

class CoursePassingGradeDetailDTO
{
    public int $id;
    public int $majorId;
    public string $majorName;
    public string $majorSystemName;
    public ?string $majorLabel;
    public int $semesterId;
    public string $semesterName;
    public ?string $semesterDescription;
    public int $courseId;
    public string $courseName;
    public string $courseCode;
    public ?string $courseDescription;
    public float $gradeValue;
    public string $createdAt;
    public string $updatedAt;
    public array $major;
    public array $semester;
    public array $course;

    public function __construct(
        int $id,
        int $majorId,
        string $majorName,
        string $majorSystemName,
        ?string $majorLabel,
        int $semesterId,
        string $semesterName,
        ?string $semesterDescription,
        int $courseId,
        string $courseName,
        string $courseCode,
        ?string $courseDescription,
        float $gradeValue,
        string $createdAt,
        string $updatedAt,
        array $major,
        array $semester,
        array $course
    ) {
        $this->id = $id;
        $this->majorId = $majorId;
        $this->majorName = $majorName;
        $this->majorSystemName = $majorSystemName;
        $this->majorLabel = $majorLabel;
        $this->semesterId = $semesterId;
        $this->semesterName = $semesterName;
        $this->semesterDescription = $semesterDescription;
        $this->courseId = $courseId;
        $this->courseName = $courseName;
        $this->courseCode = $courseCode;
        $this->courseDescription = $courseDescription;
        $this->gradeValue = $gradeValue;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->major = $major;
        $this->semester = $semester;
        $this->course = $course;
    }

    /**
     * Create DTO from CoursePassingGrade model.
     */
    public static function fromModel(CoursePassingGrade $passingGrade): self
    {
        $major = $passingGrade->major;
        $semester = $passingGrade->semester;
        $course = $passingGrade->course;

        return new self(
            $passingGrade->id,
            $passingGrade->major_id,
            $major->display_name,
            $major->system_name,
            $major->label,
            $passingGrade->semester_id,
            $semester->name,
            $semester->description,
            $passingGrade->course_id,
            $course->name,
            $course->code,
            $course->description,
            (float) $passingGrade->grade_value,
            $passingGrade->created_at->toISOString(),
            $passingGrade->updated_at->toISOString(),
            [
                'id' => $major->id,
                'system_name' => $major->system_name,
                'label' => $major->label,
                'display_name' => $major->display_name,
            ],
            [
                'id' => $semester->id,
                'name' => $semester->name,
                'description' => $semester->description,
                'start_date' => $semester->start_date,
                'end_date' => $semester->end_date,
            ],
            [
                'id' => $course->id,
                'name' => $course->name,
                'code' => $course->code,
                'description' => $course->description,
                'credits' => $course->credits,
            ]
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
            'major_system_name' => $this->majorSystemName,
            'major_label' => $this->majorLabel,
            'semester_id' => $this->semesterId,
            'semester_name' => $this->semesterName,
            'semester_description' => $this->semesterDescription,
            'course_id' => $this->courseId,
            'course_name' => $this->courseName,
            'course_code' => $this->courseCode,
            'course_description' => $this->courseDescription,
            'grade_value' => $this->gradeValue,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'major' => $this->major,
            'semester' => $this->semester,
            'course' => $this->course,
        ];
    }
}
