<?php

namespace App\DTOs\Holiday;

class HolidayCreateDTO
{
    public string $date;
    public ?string $name;

    public function __construct(string $date, ?string $name = null)
    {
        $this->date = $date;
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'name' => $this->name,
        ];
    }
}
