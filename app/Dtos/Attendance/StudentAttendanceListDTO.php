<?php

namespace App\DTOs\Attendance;

class StudentAttendanceListDTO
{
    public int $id;
    public string $student_id;
    public string $first_name;
    public string $father_name;
    public string $last_name;
    public string $major;
    public string $email;
    public string $campus;
    public ?string $attendance; // can be null or a string

    public function __construct(
        int $id,
        string $student_id,
        string $first_name,
        string $father_name,
        string $last_name,
        string $major,
        string $email,
        string $campus,
        ?string $attendance
    ) {
        $this->id           = $id;
        $this->student_id   = $student_id;
        $this->first_name   = $first_name;
        $this->father_name  = $father_name;
        $this->last_name    = $last_name;
        $this->major        = $major;
        $this->email        = $email;
        $this->campus       = $campus;
        $this->attendance   = $attendance;
    }

    /**
     * Create an AttendanceDTO instance from a database row.
     *
     * @param  object  $row
     * @return self
     */
    public static function fromDatabaseRow($row): self
    {
        return new self(
            (int)$row->id,
            $row->student_id,
            $row->first_name,
            $row->father_name,
            $row->last_name,
            $row->major,
            $row->email,
            $row->campus,
            $row->attendance // This is the alias we use for attendances.value in the query.
        );
    }
}
