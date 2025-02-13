<?php

namespace App\DTOs\Semester;

class SemesterListDTO
{
    public int $id;
    public string $name;
    public string $startDate;
    public string $endDate;
    public ?array $holidays;

    public function __construct(int $id, string $name, string $startDate, string $endDate, ?array $holidays = null)
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->holidays   = $holidays;
    }

    /**
     * Create a SemesterListDTO instance from a Semester model.
     */
    public static function fromModel($semester): self
    {
        $holidays = null;
        if ($semester->relationLoaded('holidays') && $semester->holidays) {
            $holidays = $semester->holidays->map(function ($holiday) {
                return [
                    'id'   => $holiday->id,
                    'date' => $holiday->date,
                    'name' => $holiday->name,
                ];
            })->toArray();
        }

        return new self(
            $semester->id,
            $semester->name,
            $semester->start_date,
            $semester->end_date,
            $holidays
        );
    }
}
