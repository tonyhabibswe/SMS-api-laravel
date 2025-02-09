<?php

namespace App\DTOs\Semester;

class SemesterEditDTO
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
