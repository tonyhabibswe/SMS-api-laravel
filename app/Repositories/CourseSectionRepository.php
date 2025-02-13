<?php

namespace App\Repositories;

use App\DTOs\CourseSection\CourseSectionEditDTO;
use App\Models\Attendance;
use App\Models\CourseSection;
use Illuminate\Support\Collection;

class CourseSectionRepository
{
    public function findCourseBySectionId(int $id)
    {
        return CourseSection::with('course')->find($id);

    }
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

    /**
     * Create a new CourseSection record.
     *
     * @param array $attributes
     * @return CourseSection
     */
    public function createCourseSection(array $attributes)
    {
        return CourseSection::create($attributes);
    }

    /**
     * Attach generated sessions to a given CourseSection.
     *
     * @param CourseSection $courseSection
     * @param array $sessions  Array of CourseSession instances.
     * @return void
     */
    public function attachSessions($courseSection, array $sessions): void
    {
        $courseSection->sessions()->saveMany($sessions);
    }

    /**
     * Update the course section based on the provided DTO.
     *
     * @param CourseSectionEditDTO $dto
     * @return CourseSection|null
     */
    public function updateCourseSection(CourseSectionEditDTO $dto): ?CourseSection
    {
        $courseSection = CourseSection::find($dto->id);
        if (!$courseSection) {
            return null;
        }

        $courseSection->section_code = $dto->sectionCode;
        $courseSection->save();

        return $courseSection;
    }

    /**
     * Delete the course section with the given ID.
     *
     * @param int $id
     * @return bool True if deletion was successful; false if not found.
     */
    public function deleteCourseSection(int $id): bool
    {
        $courseSection = CourseSection::find($id);
        if (!$courseSection) {
            return false;
        }
        $courseSection->delete();
        return true;
    }

    /**
     * Retrieve distinct student IDs for attendance records that belong to a given course section.
     *
     * @param int $courseSectionId
     * @return \Illuminate\Support\Collection
     */
    public function getDistinctStudentIdsByCourseSection(int $courseSectionId)
    {
        return Attendance::whereHas('session', function ($query) use ($courseSectionId) {
                    $query->where('course_section_id', $courseSectionId);
                })
                ->distinct()
                ->pluck('student_id');
    }
}
