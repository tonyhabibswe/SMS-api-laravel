<?php

namespace App\DTOs\CourseSection;

class GradesTableDTO
{
    /**
     * @param GradesTableColumnDTO[] $columns
     * @param GradesTableRowDTO[] $rows
     */
    public function __construct(
        public int $courseSectionId,
        public string $courseName,
        public string $semesterName,
        public int $totalStudents,
        public float $classAverage,
        public ?string $curveAlgorithm,
        public float $curveAdjustment,
        public array $columns,
        public array $rows
    ) {}

    public function toArray(): array
    {
        return [
            'courseSectionId' => $this->courseSectionId,
            'courseName' => $this->courseName,
            'semesterName' => $this->semesterName,
            'totalStudents' => $this->totalStudents,
            'classAverage' => $this->classAverage,
            'curveAlgorithm' => $this->curveAlgorithm,
            'curveAdjustment' => $this->curveAdjustment,
            'columns' => array_map(fn($col) => $col->toArray(), $this->columns),
            'rows' => array_map(fn($row) => $row->toArray(), $this->rows),
        ];
    }
}
