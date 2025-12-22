<?php

namespace App\DTOs\CourseSection;

class CourseSectionEditDTO
{
    public int $id;
    public string $sectionCode;
    public ?string $curveAlgorithm;

    public function __construct(int $id, string $sectionCode, ?string $curveAlgorithm = null)
    {
        $this->id = $id;
        $this->sectionCode = $sectionCode;
        $this->curveAlgorithm = $curveAlgorithm;
    }
}
