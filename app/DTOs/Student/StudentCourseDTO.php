<?php

namespace App\DTOs\Student;

/**
 * One course section a student took, with its semester, the student's status,
 * grade and attendance in it.
 */
class StudentCourseDTO
{
    public function __construct(
        public int $enrollmentId,
        public int $courseSectionId,
        public string $courseCode,
        public string $courseName,
        public string $sectionCode,
        public ?string $time,
        public int $semesterId,
        public string $semesterName,
        public string $semesterStartDate,
        public string $semesterEndDate,
        public string $status,
        public string $statusLabel,
        public ?float $finalGrade,
        public ?string $letterGrade,
        public int $absences,
        public int $sessionsMarked,
        public int $totalSessions
    ) {}

    /**
     * Create a DTO from a row of StudentRepository::getCourseHistory() and the
     * grades the section's gradebook shows for it.
     *
     * @param object $row
     * @param float|null $finalGrade
     * @param string|null $letterGrade
     * @return self
     */
    public static function fromRow(object $row, ?float $finalGrade, ?string $letterGrade): self
    {
        return new self(
            (int) $row->enrollment_id,
            (int) $row->course_section_id,
            (string) $row->course_code,
            (string) $row->course_name,
            (string) $row->section_code,
            $row->time,
            (int) $row->semester_id,
            (string) $row->semester_name,
            (string) $row->semester_start_date,
            (string) $row->semester_end_date,
            $row->status_name ?? 'enrolled',
            $row->status_label ?? 'Enrolled',
            $finalGrade,
            $letterGrade,
            (int) $row->absences,
            (int) $row->sessions_marked,
            (int) $row->total_sessions
        );
    }
}
