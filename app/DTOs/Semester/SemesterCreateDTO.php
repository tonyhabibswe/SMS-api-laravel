<?php

namespace App\DTOs\Semester;

class SemesterCreateDTO
{
    public string $name;
    public string $startDate;
    public string $endDate;
    public ?array $holidays;

    public function __construct(string $name, string $startDate, string $endDate, ?array $holidays = null)
    {
        $this->name = $name;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->holidays = $holidays;
    }
}
