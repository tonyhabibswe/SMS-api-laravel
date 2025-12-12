<?php

namespace App\DTOs\CourseEnrollment;

use App\Models\CourseEnrollment;

class CourseEnrollmentListDTO
{
    public int $id;
    public int $courseSectionId;
    public string $courseSectionName;
    public int $studentId;
    public string $studentFullName;
    public string $studentEmail;
    public int $majorId;
    public string $majorName;
    public int $statusId;
    public string $statusName;
    public string $statusLabel;
    public ?float $finalGrade;
    public ?string $letterGrade;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        int $id,
        int $courseSectionId,
        string $courseSectionName,
        int $studentId,
        string $studentFullName,
        string $studentEmail,
        int $majorId,
        string $majorName,
        int $statusId,
        string $statusName,
        string $statusLabel,
        ?float $finalGrade,
        ?string $letterGrade,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->courseSectionId = $courseSectionId;
        $this->courseSectionName = $courseSectionName;
        $this->studentId = $studentId;
        $this->studentFullName = $studentFullName;
        $this->studentEmail = $studentEmail;
        $this->majorId = $majorId;
        $this->majorName = $majorName;
        $this->statusId = $statusId;
        $this->statusName = $statusName;
        $this->statusLabel = $statusLabel;
        $this->finalGrade = $finalGrade;
        $this->letterGrade = $letterGrade;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create DTO from CourseEnrollment model.
     */
    public static function fromModel(CourseEnrollment $enrollment): self
    {
        $courseSection = $enrollment->courseSection;
        $course = $courseSection->course;
        $semester = $courseSection->semester;

        return new self(
            $enrollment->id,
            $enrollment->course_section_id,
            "{$course->code} - {$course->name} ({$semester->name})",
            $enrollment->student_id,
            $enrollment->student->full_name,
            $enrollment->student->email,
            $enrollment->major_id,
            $enrollment->major->display_name,
            $enrollment->status_id,
            $enrollment->status?->name ?? 'unknown',
            $enrollment->status?->label ?? 'Unknown',
            $enrollment->final_grade,
            $enrollment->letter_grade,
            $enrollment->created_at->toISOString(),
            $enrollment->updated_at->toISOString()
        );
    }

    /**
     * Convert DTO to array for API response.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course_section_id' => $this->courseSectionId,
            'course_section_name' => $this->courseSectionName,
            'student_id' => $this->studentId,
            'student_full_name' => $this->studentFullName,
            'student_email' => $this->studentEmail,
            'major_id' => $this->majorId,
            'major_name' => $this->majorName,
            'status_id' => $this->statusId,
            'status_name' => $this->statusName,
            'status_label' => $this->statusLabel,
            'final_grade' => $this->finalGrade,
            'letter_grade' => $this->letterGrade,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
