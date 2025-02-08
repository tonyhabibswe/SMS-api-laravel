<?php

namespace App\DTOs;

class SemesterListDTO
{
    public int $id;
    public string $name;
    public string $start_date;
    public string $end_date;
    public ?array $holidays;

    public function __construct(int $id, string $name, string $start_date, string $end_date, ?array $holidays = null)
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->start_date = $start_date;
        $this->end_date   = $end_date;
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
            $semester->start_date->toDateString(),
            $semester->end_date->toDateString(),
            $holidays
        );
    }
}
