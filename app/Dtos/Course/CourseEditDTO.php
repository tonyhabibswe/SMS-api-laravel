<?php

namespace App\DTOs\Course;

class CourseEditDTO
{
    public int $id;
    public string $code;
    public string $name;

    public function __construct(int $id, string $code, string $name)
    {
        $this->id   = $id;
        $this->code = $code;
        $this->name = $name;
    }
}
