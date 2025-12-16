<?php

namespace App\DTOs\GradeableCategory;

use App\Models\GradeableCategory;

class GradeableCategoryListDTO
{
    public function __construct(
        public int $id,
        public int $courseSectionId,
        public ?string $courseSectionName,
        public string $name,
        public float $weightPercent,
        public string $algorithm,
        public int $itemsCount
    ) {}

    public static function fromModel(GradeableCategory $category): self
    {
        return new self(
            id: $category->id,
            courseSectionId: $category->course_section_id,
            courseSectionName: $category->courseSection->section_code ?? null,
            name: $category->name,
            weightPercent: (float) $category->weight_percent,
            algorithm: $category->algorithm,
            itemsCount: $category->gradeableItems->count()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'courseSectionId' => $this->courseSectionId,
            'courseSectionName' => $this->courseSectionName,
            'name' => $this->name,
            'weightPercent' => $this->weightPercent,
            'algorithm' => $this->algorithm,
            'itemsCount' => $this->itemsCount,
        ];
    }
}
