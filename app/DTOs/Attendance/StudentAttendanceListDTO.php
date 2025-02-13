<?php

namespace App\DTOs\Attendance;

class StudentAttendanceListDTO
{
    public int $id;
    public string $studentId;
    public string $firstName;
    public string $fatherName;
    public string $lastName;
    public string $major;
    public string $email;
    public string $campus;
    public ?string $attendance; // can be null or a string

    public function __construct(
        int $id,
        string $studentId,
        string $firstName,
        string $fatherName,
        string $lastName,
        string $major,
        string $email,
        string $campus,
        ?string $attendance
    ) {
        $this->id           = $id;
        $this->studentId   = $studentId;
        $this->firstName   = $firstName;
        $this->fatherName  = $fatherName;
        $this->lastName    = $lastName;
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
