<?php

namespace App\DTOs\Student;

use App\Models\StudentMajorHistory;

class StudentMajorHistoryDTO
{
    public int $id;
    public int $studentId;
    public int $majorId;
    public int $semesterId;
    public string $majorSystemName;
    public ?string $majorLabel;
    public string $majorDisplayName;
    public string $semesterName;
    public string $studentName;
    public string $createdAt;

    public function __construct(
        int $id,
        int $studentId,
        int $majorId,
        int $semesterId,
        string $majorSystemName,
        ?string $majorLabel,
        string $majorDisplayName,
        string $semesterName,
        string $studentName,
        string $createdAt
    ) {
        $this->id = $id;
        $this->studentId = $studentId;
        $this->majorId = $majorId;
        $this->semesterId = $semesterId;
        $this->majorSystemName = $majorSystemName;
        $this->majorLabel = $majorLabel;
        $this->majorDisplayName = $majorDisplayName;
        $this->semesterName = $semesterName;
        $this->studentName = $studentName;
        $this->createdAt = $createdAt;
    }

    /**
     * Create a DTO from a StudentMajorHistory model.
     *
     * @param StudentMajorHistory $history
     * @return self
     */
    public static function fromModel(StudentMajorHistory $history): self
    {
        $student = $history->student;
        $major = $history->major;
        $semester = $history->semester;

        return new self(
            $history->id,
            $history->student_id,
            $history->major_id,
            $history->semester_id,
            $major->system_name,
            $major->label,
            $major->display_name,
            $semester->name,
            "{$student->first_name} {$student->father_name} {$student->last_name}",
            $history->created_at->toISOString()
        );
    }

    /**
     * Convert to array for API responses.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->studentId,
            'major_id' => $this->majorId,
            'semester_id' => $this->semesterId,
            'major' => [
                'id' => $this->majorId,
                'system_name' => $this->majorSystemName,
                'label' => $this->majorLabel,
                'display_name' => $this->majorDisplayName,
            ],
            'semester_name' => $this->semesterName,
            'student_name' => $this->studentName,
            'created_at' => $this->createdAt,
        ];
    }
}
