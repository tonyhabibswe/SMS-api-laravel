<?php

namespace App\DTOs\CourseSection;

class CourseSectionEditDTO
{
    public int $id;
    public string $sectionCode;
    public ?string $curveAlgorithm;
    // Whether the request sent curveAlgorithm; sent as null, it removes the curve
    public bool $curveAlgorithmSent;

    public function __construct(int $id, string $sectionCode, ?string $curveAlgorithm, bool $curveAlgorithmSent)
    {
        $this->id = $id;
        $this->sectionCode = $sectionCode;
        $this->curveAlgorithm = $curveAlgorithm;
        $this->curveAlgorithmSent = $curveAlgorithmSent;
    }
}
