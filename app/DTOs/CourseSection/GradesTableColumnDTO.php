<?php

namespace App\DTOs\CourseSection;

class GradesTableColumnDTO
{
    public function __construct(
        public string $key,
        public string $label,
        public string $type, // identifier, gradeableItem, category, calculated
        public ?int $gradeableItemId = null,
        public ?int $categoryId = null,
        public ?string $categoryName = null,
        public ?float $maxPoints = null,
        public ?float $weightPercent = null,
        public ?string $algorithm = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
        ];

        if ($this->gradeableItemId !== null) {
            $data['gradeableItemId'] = $this->gradeableItemId;
        }

        if ($this->categoryId !== null) {
            $data['categoryId'] = $this->categoryId;
        }

        if ($this->categoryName !== null) {
            $data['categoryName'] = $this->categoryName;
        }

        if ($this->maxPoints !== null) {
            $data['maxPoints'] = $this->maxPoints;
        }

        if ($this->weightPercent !== null) {
            $data['weightPercent'] = $this->weightPercent;
        }

        if ($this->algorithm !== null) {
            $data['algorithm'] = $this->algorithm;
        }

        return $data;
    }
}
