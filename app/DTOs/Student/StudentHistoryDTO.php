<?php

namespace App\DTOs\Student;

/**
 * A student and every course section they took, newest semester first.
 */
class StudentHistoryDTO
{
    /**
     * @param StudentCourseDTO[] $courses
     */
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
        public array $courses
    ) {}
}
