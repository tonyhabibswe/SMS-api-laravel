<?php

namespace App\DTOs\GradeableItem;

use App\Models\GradeableItem;

class GradeableItemListDTO
{
    public function __construct(
        public int $id,
        public int $categoryId,
        public ?string $categoryName,
        public string $title,
        public float $maxPoints,
        public int $gradesCount
    ) {}

    public static function fromModel(GradeableItem $item): self
    {
        return new self(
            id: $item->id,
            categoryId: $item->category_id,
            categoryName: $item->category->name ?? null,
            title: $item->title,
            maxPoints: (float) $item->max_points,
            gradesCount: $item->grades->count()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'title' => $this->title,
            'maxPoints' => $this->maxPoints,
            'gradesCount' => $this->gradesCount,
        ];
    }
}
