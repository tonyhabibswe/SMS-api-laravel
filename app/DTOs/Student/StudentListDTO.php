<?php

namespace App\DTOs\Student;

/**
 * A student in the students list, with how many course sections they took.
 */
class StudentListDTO
{
    public function __construct(
        public int $id,
        public string $studentId,
        public string $firstName,
        public string $fatherName,
        public string $lastName,
        public ?string $major,
        public string $email,
        public string $campus,
        public int $coursesCount,
        public int $semestersCount,
        public ?int $lastSemesterId,
        public ?string $lastSemesterName,
        public ?string $lastSemesterStartDate
    ) {}

    /**
     * Create a DTO from a row of StudentRepository::getStudentsWithCourseCounts().
     *
     * @param object $row
     * @return self
     */
    public static function fromRow(object $row): self
    {
        return new self(
            (int) $row->id,
            (string) $row->student_id,
            (string) $row->first_name,
            (string) $row->father_name,
            (string) $row->last_name,
            $row->major,
            (string) $row->email,
            (string) $row->campus,
            (int) $row->courses_count,
            (int) $row->semesters_count,
            $row->last_semester_id !== null ? (int) $row->last_semester_id : null,
            $row->last_semester_name,
            $row->last_semester_start_date
        );
    }
}
