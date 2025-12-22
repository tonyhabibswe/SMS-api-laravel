<?php

namespace App\DTOs\CourseSection;

class GradesTableRowDTO
{
    public function __construct(
        public int $enrollmentId,
        public string $studentId,
        public string $studentName,
        public string $enrollmentStatus,
        public array $grades, // Dynamic keys for item_X and category_X
        public ?float $finalGrade,
        public ?string $letterGrade,
        public ?float $curvedFinalGrade,
        public ?string $curvedLetterGrade
    ) {}

    public function toArray(): array
    {
        return array_merge([
            'enrollmentId' => $this->enrollmentId,
            'studentId' => $this->studentId,
            'studentName' => $this->studentName,
            'enrollmentStatus' => $this->enrollmentStatus,
        ], $this->grades, [
            'finalGrade' => $this->finalGrade,
            'letterGrade' => $this->letterGrade,
            'curvedFinalGrade' => $this->curvedFinalGrade,
            'curvedLetterGrade' => $this->curvedLetterGrade,
        ]);
    }
}
