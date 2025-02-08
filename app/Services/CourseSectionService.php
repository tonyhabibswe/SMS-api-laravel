<?php

namespace App\Services;

use App\DTOs\CourseSection\CourseSectionListDTO;
use App\Repositories\CourseSectionRepository;
use Illuminate\Support\Collection;

class CourseSectionService
{
    protected CourseSectionRepository $courseSectionRepository;

    public function __construct(CourseSectionRepository $courseSectionRepository)
    {
        $this->courseSectionRepository = $courseSectionRepository;
    }

    /**
     * List course sections by semester ID as a collection of DTOs.
     *
     * @param int $semesterId
     * @return Collection|CourseSectionListDTO[]
     */
    public function listBySemesterId(int $semesterId): Collection
    {
        $courseSections = $this->courseSectionRepository->getBySemesterId($semesterId);

        return $courseSections->map(function ($courseSection) {
            return CourseSectionListDTO::fromModel($courseSection);
        });
    }
}
