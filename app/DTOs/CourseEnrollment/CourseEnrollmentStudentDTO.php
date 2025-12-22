<?php

namespace App\DTOs\CourseEnrollment;

use App\Models\CourseEnrollment;

class CourseEnrollmentStudentDTO
{
    public int $id;
    public int $studentId;
    public string $firstName;
    public string $fatherName;
    public string $lastName;
    public string $major;
    public string $status;

    public function __construct(
        int $id,
        int $studentId,
        string $firstName,
        string $fatherName,
        string $lastName,
        string $major,
        string $status
    ) {
        $this->id = $id;
        $this->studentId = $studentId;
        $this->firstName = $firstName;
        $this->fatherName = $fatherName;
        $this->lastName = $lastName;
        $this->major = $major;
        $this->status = $status;
    }

    /**
     * Create DTO from CourseEnrollment model.
     */
    public static function fromModel(CourseEnrollment $enrollment): self
    {
        return new self(
            $enrollment->id,
            $enrollment->student_id,
            $enrollment->student->first_name,
            $enrollment->student->father_name,
            $enrollment->student->last_name,
            $enrollment->major->display_name,
            $enrollment->status?->name ?? 'unknown'
        );
    }

    /**
     * Convert DTO to array for API response.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'studentId' => $this->studentId,
            'firstName' => $this->firstName,
            'fatherName' => $this->fatherName,
            'lastName' => $this->lastName,
            'major' => $this->major,
            'status' => $this->status,
        ];
    }
}
