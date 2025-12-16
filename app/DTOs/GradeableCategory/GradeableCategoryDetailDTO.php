<?php

namespace App\DTOs\GradeableCategory;

use App\Models\GradeableCategory;

class GradeableCategoryDetailDTO
{
    public function __construct(
        public int $id,
        public int $courseSectionId,
        public ?string $courseSectionName,
        public string $name,
        public float $weightPercent,
        public string $algorithm,
        public array $items,
        public string $createdAt,
        public string $updatedAt
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
            items: $category->gradeableItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'maxPoints' => (float) $item->max_points,
                ];
            })->toArray(),
            createdAt: $category->created_at->toISOString(),
            updatedAt: $category->updated_at->toISOString()
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
            'items' => $this->items,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
