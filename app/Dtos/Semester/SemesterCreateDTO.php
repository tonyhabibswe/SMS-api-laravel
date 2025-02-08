<?php

namespace App\DTOs\Semester;

class SemesterCreateDTO
{
    public string $name;
    public string $start_date;
    public string $end_date;
    public ?array $holidays;

    public function __construct(string $name, string $start_date, string $end_date, ?array $holidays = null)
    {
        $this->name = $name;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->holidays = $holidays;
    }
}
