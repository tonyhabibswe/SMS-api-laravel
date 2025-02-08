<?php

namespace App\DTOs\CourseSection;

class CourseSectionEditDTO
{
    public int $id;
    public string $section_code;

    public function __construct(int $id, string $section_code)
    {
        $this->id = $id;
        $this->section_code = $section_code;
    }
}
