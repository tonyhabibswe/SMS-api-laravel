<?php

namespace App\DTOs\Student;

class StudentCreateDTO
{
    public string $student_id;
    public string $first_name;
    public string $father_name;
    public string $last_name;
    public string $major;
    public string $email;
    public string $campus;

    public function __construct(
        string $student_id,
        string $first_name,
        string $father_name,
        string $last_name,
        string $major,
        string $email,
        string $campus
    ) {
        $this->student_id  = $student_id;
        $this->first_name  = $first_name;
        $this->father_name = $father_name;
        $this->last_name   = $last_name;
        $this->major       = $major;
        $this->email       = $email;
        $this->campus      = $campus;
    }
}
