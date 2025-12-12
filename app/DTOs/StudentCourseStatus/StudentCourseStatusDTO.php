<?php

namespace App\DTOs\StudentCourseStatus;

use App\Models\StudentCourseStatus;

class StudentCourseStatusDTO
{
    public int $id;
    public string $name;
    public string $label;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        int $id,
        string $name,
        string $label,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->label = $label;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create DTO from StudentCourseStatus model.
     */
    public static function fromModel(StudentCourseStatus $status): self
    {
        return new self(
            $status->id,
            $status->name,
            $status->label,
            $status->created_at->toISOString(),
            $status->updated_at->toISOString()
        );
    }

    /**
     * Convert DTO to array for API response.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
