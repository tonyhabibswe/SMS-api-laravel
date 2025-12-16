<?php

namespace App\DTOs\GradeableCategory;

class GradeableCategoryCreateDTO
{
    public function __construct(
        public int $courseSectionId,
        public string $name,
        public float $weightPercent,
        public string $algorithm
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            courseSectionId: $data['courseSectionId'],
            name: $data['name'],
            weightPercent: (float) $data['weightPercent'],
            algorithm: $data['algorithm']
        );
    }
}
