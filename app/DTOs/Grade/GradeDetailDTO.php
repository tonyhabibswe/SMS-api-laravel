<?php

namespace App\DTOs\Grade;

use App\Models\Grade;

class GradeDetailDTO
{
    public function __construct(
        public int $id,
        public int $gradeableItemId,
        public ?string $gradeableItemTitle,
        public ?float $maxPoints,
        public int $courseEnrollmentId,
        public ?int $studentId,
        public ?string $studentName,
        public ?float $gradeValue,
        public ?float $percentage,
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromModel(Grade $grade): self
    {
        $student = $grade->courseEnrollment->student ?? null;

        return new self(
            id: $grade->id,
            gradeableItemId: $grade->gradeable_item_id,
            gradeableItemTitle: $grade->gradeableItem->title ?? null,
            maxPoints: $grade->gradeableItem ? (float) $grade->gradeableItem->max_points : null,
            courseEnrollmentId: $grade->course_enrollment_id,
            studentId: $student?->id,
            studentName: $student ? trim($student->first_name . ' ' . $student->last_name) : null,
            gradeValue: $grade->grade_value ? (float) $grade->grade_value : null,
            percentage: $grade->percentage,
            createdAt: $grade->created_at->toISOString(),
            updatedAt: $grade->updated_at->toISOString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'gradeableItemId' => $this->gradeableItemId,
            'gradeableItemTitle' => $this->gradeableItemTitle,
            'maxPoints' => $this->maxPoints,
            'courseEnrollmentId' => $this->courseEnrollmentId,
            'studentId' => $this->studentId,
            'studentName' => $this->studentName,
            'gradeValue' => $this->gradeValue,
            'percentage' => $this->percentage,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
