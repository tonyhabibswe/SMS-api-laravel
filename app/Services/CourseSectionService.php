<?php

namespace App\Services;

use App\DTOs\CourseSection\CourseSectionCreateDTO;
use App\DTOs\CourseSection\CourseSectionEditDTO;
use App\DTOs\CourseSection\CourseSectionListDTO;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Semester;
use App\Repositories\CourseSectionRepository;
use Carbon\Carbon;
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

    /**
     * Create a new CourseSection with generated sessions.
     *
     * @param CourseSectionCreateDTO $dto
     * @return \App\Models\CourseSection
     * @throws \Exception if related Semester or Course is not found.
     */
    public function createCourseSection(CourseSectionCreateDTO $dto)
    {
        // Retrieve the Semester (with holidays) by ID.
        $semester = Semester::with('holidays')->find($dto->semesterId);
        if (!$semester) {
            throw new \Exception("Semester not found", 404);
        }

        // Retrieve the Course by ID.
        $course = Course::find($dto->courseId);
        if (!$course) {
            throw new \Exception("Course not found", 404);
        }

        // Prepare an array of holiday dates.
        $holidayDates = $semester->holidays->pluck('date')->toArray();
        // Build a formatted time string for the section.
        $courseSectionTime = implode('', array_map('getDayAbbreviation', $dto->courseDays))
            . " {$dto->startSessionTime}-{$dto->endSessionTime}";

        // Create the CourseSection.
        $courseSection = $this->courseSectionRepository->createCourseSection([
            'course_id'    => $course->id,
            'semester_id'  => $semester->id,
            'section_code' => $dto->sectionCode,
            'time'         => $courseSectionTime,
        ]);

        // Parse the start and end dates of the semester to carbon date objects.
        $startDate = Carbon::parse($semester->start_date);
        $endDate = Carbon::parse($semester->end_date);

        // Generate sessions for each day between the semester's start and end dates.
        $sessions = [];
        $diffDays = $startDate->diffInDays($endDate);
        for ($dayOffset = 0; $dayOffset <= $diffDays; $dayOffset++) {
            $date = $startDate->copy()->addDays($dayOffset);

            // Skip if this date is a holiday.
            if (in_array($date->toDateString(), $holidayDates)) {
                continue;
            }

            // Check if the day of week is one of the course days.
            if (in_array($date->format('l'), $dto->courseDays)) {
                $startDateTime = $date->copy()->setTimeFromTimeString($dto->startSessionTime);
                $endDateTime   = $date->copy()->setTimeFromTimeString($dto->endSessionTime);

                $sessions[] = new CourseSession([
                    'session_start' => $startDateTime,
                    'session_end'   => $endDateTime,
                    'room'          => $dto->room,
                ]);
            }
        }

        // Save the generated sessions.
        $this->courseSectionRepository->attachSessions($courseSection, $sessions);

        // Optionally, load sessions for returning.
        $courseSection->load('sessions');

        return $courseSection;
    }

    /**
     * Update a course section and return its updated DTO.
     *
     * @param CourseSectionEditDTO $dto
     * @return CourseSectionListDTO|null
     */
    public function updateCourseSection(CourseSectionEditDTO $dto): ?CourseSectionListDTO
    {
        $courseSection = $this->courseSectionRepository->updateCourseSection($dto);

        if (!$courseSection) {
            return null;
        }
        
        // Optionally load any relationships if needed.
        $courseSection->load('course', 'sessions');
        // Convert the updated model into a DTO for output.
        return CourseSectionListDTO::fromModel($courseSection);
    }

    /**
     * Delete a course section by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCourseSection(int $id): bool
    {
        return $this->courseSectionRepository->deleteCourseSection($id);
    }
}
