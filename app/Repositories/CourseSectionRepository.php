<?php

namespace App\Repositories;

use App\Models\CourseSection;
use Illuminate\Support\Collection;

class CourseSectionRepository
{
    /**
     * Retrieve all course sections by semester ID with necessary relationships.
     *
     * @param int $semesterId
     * @return Collection
     */
    public function getBySemesterId(int $semesterId): Collection
    {
        return CourseSection::where('semester_id', $semesterId)
            ->with([
                'firstSession',           // Assuming a relationship that retrieves the first session
                'course:id,code,name'     // Eager load only the needed columns from the courses table
            ])
            ->get();
    }
}
