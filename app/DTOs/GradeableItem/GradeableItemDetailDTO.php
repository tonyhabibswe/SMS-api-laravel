<?php

namespace App\DTOs\GradeableItem;

use App\Models\GradeableItem;

class GradeableItemDetailDTO
{
    public function __construct(
        public int $id,
        public int $categoryId,
        public ?string $categoryName,
        public ?string $categoryAlgorithm,
        public ?float $categoryWeight,
        public string $title,
        public float $maxPoints,
        public int $gradesCount,
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromModel(GradeableItem $item): self
    {
        return new self(
            id: $item->id,
            categoryId: $item->category_id,
            categoryName: $item->category->name ?? null,
            categoryAlgorithm: $item->category->algorithm ?? null,
            categoryWeight: $item->category ? (float) $item->category->weight_percent : null,
            title: $item->title,
            maxPoints: (float) $item->max_points,
            gradesCount: $item->grades->count(),
            createdAt: $item->created_at->toISOString(),
            updatedAt: $item->updated_at->toISOString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'categoryAlgorithm' => $this->categoryAlgorithm,
            'categoryWeight' => $this->categoryWeight,
            'title' => $this->title,
            'maxPoints' => $this->maxPoints,
            'gradesCount' => $this->gradesCount,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
