<?php

namespace App\DTOs\CourseSection;

class CourseSectionEditDTO
{
    public int $id;
    public string $sectionCode;

    public function __construct(int $id, string $sectionCode)
    {
        $this->id = $id;
        $this->sectionCode = $sectionCode;
    }
}
