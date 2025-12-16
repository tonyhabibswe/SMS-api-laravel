<?php

namespace App\DTOs\GradeableCategory;

class GradeableCategoryUpdateDTO
{
    public function __construct(
        public ?string $name = null,
        public ?float $weightPercent = null,
        public ?string $algorithm = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            weightPercent: isset($data['weightPercent']) ? (float) $data['weightPercent'] : null,
            algorithm: $data['algorithm'] ?? null
        );
    }
}
