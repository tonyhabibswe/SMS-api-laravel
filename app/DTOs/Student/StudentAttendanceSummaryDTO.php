<?php

namespace App\DTOs\Student;

class StudentAttendanceSummaryDTO
{
    public int $id;
    public string $studentId;
    public string $firstName;
    public string $fatherName;
    public string $lastName;
    public string $major;
    public string $email;
    public string $campus;
    public int $abscences;
    public int $sessions;
    public int $totalSessions;

    public function __construct(
        int $id,
        string $studentId,
        string $firstName,
        string $fatherName,
        string $lastName,
        string $major,
        string $email,
        string $campus,
        int $abscences,
        int $sessions,
        int $totalSessions
    ) {
        $this->id             = $id;
        $this->studentId     = $studentId;
        $this->firstName     = $firstName;
        $this->fatherName    = $fatherName;
        $this->lastName      = $lastName;
        $this->major          = $major;
        $this->email          = $email;
        $this->campus         = $campus;
        $this->abscences      = $abscences;
        $this->sessions       = $sessions;
        $this->totalSessions = $totalSessions;
    }

    /**
     * Create a DTO from a database row.
     *
     * @param  object $row
     * @return self
     */
    public static function fromDatabaseRow($row): self
    {
        return new self(
            (int) $row->id,
            $row->student_id,
            $row->first_name,
            $row->father_name,
            $row->last_name,
            $row->major,
            $row->email,
            $row->campus,
            (int) $row->abscences,
            (int) $row->sessions,
            (int) $row->total_sessions
        );
    }
}
