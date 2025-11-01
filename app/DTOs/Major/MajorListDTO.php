<?php

namespace App\DTOs\Major;

use App\Models\Major;

class MajorListDTO
{
    public int $id;
    public string $systemName;
    public ?string $label;
    public string $displayName;
    public ?int $studentsCount;

    public function __construct(
        int $id,
        string $systemName,
        ?string $label,
        string $displayName,
        ?int $studentsCount = null
    ) {
        $this->id = $id;
        $this->systemName = $systemName;
        $this->label = $label;
        $this->displayName = $displayName;
        $this->studentsCount = $studentsCount;
    }

    /**
     * Create a DTO from a Major model.
     *
     * @param Major $major
     * @return self
     */
    public static function fromModel(Major $major): self
    {
        return new self(
            $major->id,
            $major->system_name,
            $major->label,
            $major->display_name
        );
    }

    /**
     * Create a DTO from a Major model with student count.
     *
     * @param Major $major
     * @return self
     */
    public static function fromModelWithStudentCount(Major $major): self
    {
        return new self(
            $major->id,
            $major->system_name,
            $major->label,
            $major->display_name,
            $major->students_count ?? 0
        );
    }

    /**
     * Convert to array for API responses.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'system_name' => $this->systemName,
            'label' => $this->label,
            'display_name' => $this->displayName,
        ];

        if ($this->studentsCount !== null) {
            $data['students_count'] = $this->studentsCount;
        }

        return $data;
    }
}
