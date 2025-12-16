<?php

namespace App\DTOs\Grade;

class GradeUpdateDTO
{
    public function __construct(
        public float $gradeValue
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            gradeValue: (float) $data['gradeValue']
        );
    }
}
