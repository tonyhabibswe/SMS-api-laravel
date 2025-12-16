<?php

namespace App\DTOs\GradeableItem;

class GradeableItemUpdateDTO
{
    public function __construct(
        public ?string $title = null,
        public ?float $maxPoints = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            maxPoints: isset($data['maxPoints']) ? (float) $data['maxPoints'] : null
        );
    }
}
