<?php

namespace App\DTOs\GradeableItem;

class GradeableItemCreateDTO
{
    public function __construct(
        public int $categoryId,
        public string $title,
        public ?float $maxPoints = 100.00
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            categoryId: $data['categoryId'],
            title: $data['title'],
            maxPoints: isset($data['maxPoints']) ? (float) $data['maxPoints'] : 100.00
        );
    }
}
