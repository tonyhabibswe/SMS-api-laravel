<?php

namespace App\DTOs\Student;

class StudentCreateDTO
{
    public string $studentId;
    public string $firstName;
    public string $fatherName;
    public string $lastName;
    public string $major;
    public string $email;
    public string $campus;

    public function __construct(
        string $studentId,
        string $firstName,
        string $fatherName,
        string $lastName,
        string $major,
        string $email,
        string $campus
    ) {
        $this->studentId  = $studentId;
        $this->firstName  = $firstName;
        $this->fatherName = $fatherName;
        $this->lastName   = $lastName;
        $this->major       = $major;
        $this->email       = $email;
        $this->campus      = $campus;
    }
}
