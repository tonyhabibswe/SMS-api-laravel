<?php

namespace App\DTOs\Student;

class StudentAttendanceSummaryDTO
{
    public int $id;
    public string $student_id;
    public string $first_name;
    public string $father_name;
    public string $last_name;
    public string $major;
    public string $email;
    public string $campus;
    public int $abscences;
    public int $sessions;
    public int $total_sessions;

    public function __construct(
        int $id,
        string $student_id,
        string $first_name,
        string $father_name,
        string $last_name,
        string $major,
        string $email,
        string $campus,
        int $abscences,
        int $sessions,
        int $total_sessions
    ) {
        $this->id             = $id;
        $this->student_id     = $student_id;
        $this->first_name     = $first_name;
        $this->father_name    = $father_name;
        $this->last_name      = $last_name;
        $this->major          = $major;
        $this->email          = $email;
        $this->campus         = $campus;
        $this->abscences      = $abscences;
        $this->sessions       = $sessions;
        $this->total_sessions = $total_sessions;
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
